<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ItemService extends BaseService
{
    /**
     * Get filtered items with pagination
     * 
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredItems(array $filters)
    {
        $query = Item::with('category');

        // Apply plant filter if present
        if (!empty($filters['plant'])) {
            $query->where($query->getModel()->getTable() . '.plant_id', $this->resolvePlantId($filters['plant']));
        }

        // Apply specific ID filter (Highest Priority)
        if (!empty($filters['item_id'])) {
            $query->where('id', $filters['item_id']);
            return $query->paginate(10)->withQueryString();
        }

        // Apply global search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('customer', 'like', '%' . $search . '%')
                    ->orWhere('part_number', 'like', '%' . $search . '%')
                    ->orWhere('sap_code', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply specific search filters (fallback if needed)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['customer'])) {
            $query->where('customer', 'like', '%' . $filters['customer'] . '%');
        }

        if (!empty($filters['part_number'])) {
            $query->where('part_number', 'like', '%' . $filters['part_number'] . '%');
        }

        if (!empty($filters['sap_code'])) {
            $query->where('sap_code', 'like', '%' . $filters['sap_code'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        return $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
    }

    /**
     * Create new item
     * 
     * @param array $data
     * @return Item
     */
    public function createItem(array $data): Item
    {
        DB::beginTransaction();
        try {
            // Pre-validate uniqueness in DB to avoid moving files if SQL insert will fail
            if (!empty($data['sap_code'])) {
                $plantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id);
                if (Item::where('sap_code', $data['sap_code'])
                    ->where('plant_id', $plantId)
                    ->where('category_id', $data['category_id'])
                    ->exists()) {
                    throw new \Exception("Duplicate entry '{$data['sap_code']}' for key 'items.items_sap_code_unique'");
                }
            }

            // Handle multi-file upload
            $filePaths = [];
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePaths[] = $this->handleItemFileUpload($file, $data['customer'] ?? null);
                }
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Handle similar part file upload from data
            if (isset($data['similar_part_file']) && $data['similar_part_file'] instanceof \Illuminate\Http\UploadedFile) {
                $data['similar_part_file_path'] = $this->handleItemFileUpload($data['similar_part_file'], $data['customer'] ?? null);
            }

            // Create item
            $item = Item::create([
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? auth()->user()->plant_id),
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'file_path' => $filePaths[0] ?? null, // Fallback for legacy
                'file_paths' => $filePaths,
                'similar_part_file_path' => $data['similar_part_file_path'] ?? null,
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'cavity' => $data['cavity'] ?? 1,
                'weight_standard' => $data['weight_standard'] ?? null,
                'standard_cycle_time' => $data['standard_cycle_time'] ?? null,
                'defects' => $defects,
                'dimension_standards' => $dimensionStandards,
            ]);

            DB::commit();
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing item
     * 
     * @param int $id
     * @param array $data
     * @return Item
     */
    public function updateItem(string $id, array $data): Item
    {
        DB::beginTransaction();
        try {
            // Pre-validate uniqueness in DB to avoid moving files if SQL update will fail
            if (!empty($data['sap_code'])) {
                $itemCurrent = Item::find($id);
                $plantId = $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? ($itemCurrent ? $itemCurrent->plant_id : auth()->user()->plant_id));
                if (Item::where('sap_code', $data['sap_code'])
                    ->where('plant_id', $plantId)
                    ->where('category_id', $data['category_id'])
                    ->where('id', '!=', $id)
                    ->exists()) {
                    throw new \Exception("Duplicate entry '{$data['sap_code']}' for key 'items.items_sap_code_unique'");
                }
            }

            // Get item (with or without global scope based on role)
            if (auth()->user()->role === 'admin') {
                $item = Item::withoutGlobalScope('plant')->findOrFail($id);
            } else {
                $item = Item::findOrFail($id);
            }

            $customerChanged = $item->customer !== ($data['customer'] ?? null);
            $filePaths = $item->file_paths ?? [];

            // Handle similar part file upload from data
            if (isset($data['similar_part_file']) && $data['similar_part_file'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old file if exists
                if ($item->similar_part_file_path) {
                    $this->deleteFile($item->similar_part_file_path);
                }
                $data['similar_part_file_path'] = $this->handleItemFileUpload($data['similar_part_file'], $data['customer'] ?? null);
            } else {
                $data['similar_part_file_path'] = $item->similar_part_file_path;
            }

            // If customer changed, move all existing files
            if ($customerChanged) {
                if (!empty($filePaths)) {
                    $newFilePaths = [];
                    foreach ($filePaths as $path) {
                        $newFilePaths[] = $this->moveFilePathToCustomerFolder($path, $data['customer'] ?? null);
                    }
                    $filePaths = $newFilePaths;
                }
                if ($data['similar_part_file_path']) {
                    $data['similar_part_file_path'] = $this->moveFilePathToCustomerFolder($data['similar_part_file_path'], $data['customer'] ?? null);
                }
            }

            // Handle new standard file uploads
            if (isset($data['files'])) {
                foreach ($data['files'] as $file) {
                    $filePaths[] = $this->handleItemFileUpload($file, $data['customer'] ?? null);
                }
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Update item
            $item->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'plant_id' => $this->resolvePlantId($data['plant_id'] ?? $data['plant'] ?? $item->plant_id),
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'file_path' => $filePaths[0] ?? null,
                'file_paths' => $filePaths,
                'similar_part_file_path' => $data['similar_part_file_path'],
                'cavity' => $data['cavity'] ?? 1,
                'weight_standard' => $data['weight_standard'] ?? null,
                'standard_cycle_time' => $data['standard_cycle_time'] ?? null,
                'defects' => $defects,
                'dimension_standards' => $dimensionStandards,
            ]);

            DB::commit();
            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete item
     * 
     * @param int $id
     * @return bool
     */
    public function deleteItem(string $id): bool
    {
        DB::beginTransaction();
        try {
            $query = Item::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $item = $query->findOrFail($id);

            // Delete all associated files
            if (!empty($item->file_paths)) {
                foreach ($item->file_paths as $path) {
                    $this->deleteFile($path);
                }
            } elseif ($item->file_path) {
                $this->deleteFile($item->file_path);
            }

            if ($item->similar_part_file_path) {
                $this->deleteFile($item->similar_part_file_path);
            }

            $item->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle item file upload
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string|null $customer
     * @return string
     */
    private function handleItemFileUpload($file, ?string $customer): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $customerFolder = $this->getCustomerFolder($customer);
        $path = 'master item/' . $customerFolder;

        $uploadPath = public_path($path);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $file->move($uploadPath, $filename);

        return $path . '/' . $filename;
    }

    /**
     * Move a single file path to new customer folder
     * 
     * @param string $path
     * @param string|null $newCustomer
     * @return string
     */
    private function moveFilePathToCustomerFolder(string $path, ?string $newCustomer): string
    {
        $oldPath = public_path($path);

        if (file_exists($oldPath)) {
            $filename = basename($path);
            $customerFolder = $this->getCustomerFolder($newCustomer);
            $newPath = public_path('master item/' . $customerFolder);

            if (!file_exists($newPath)) {
                mkdir($newPath, 0755, true);
            }

            $newFilePath = $newPath . '/' . $filename;
            rename($oldPath, $newFilePath);

            return 'master item/' . $customerFolder . '/' . $filename;
        }

        return $path;
    }

    /**
     * Delete a single PDF from an item
     * 
     * @param string $id
     * @param int $index
     * @return void
     */
    public function deleteItemPdf(string $id, int $index): void
    {
        $item = Item::withoutGlobalScope('plant')->findOrFail($id);
        $filePaths = $item->file_paths;

        // Handle legacy file_path if file_paths is empty
        if (empty($filePaths) && $index == 0 && $item->file_path) {
            $this->deleteFile($item->file_path);
            $item->update([
                'file_path' => null,
                'file_paths' => []
            ]);
            return;
        }

        if (isset($filePaths[$index])) {
            $pathToDelete = $filePaths[$index];
            $this->deleteFile($pathToDelete);

            array_splice($filePaths, $index, 1);
            $filePaths = array_values($filePaths); // Ensure clean indexing

            $item->update([
                'file_paths' => $filePaths,
                'file_path' => $filePaths[0] ?? null
            ]);
        }
    }

    /**
     * Delete similar part PDF from an item
     * 
     * @param string $id
     * @return void
     */
    public function deleteItemSimilarPdf(string $id): void
    {
        // Use withoutGlobalScope if needed, similar to deleteItemPdf logic
        if (auth()->user()->role === 'admin') {
            $item = Item::withoutGlobalScope('plant')->findOrFail($id);
        } else {
            $item = Item::findOrFail($id);
        }

        if ($item->similar_part_file_path) {
            $this->deleteFile($item->similar_part_file_path);

            $item->update([
                'similar_part_file_path' => null
            ]);
        }
    }

    /**
     * Determine customer folder
     * 
     * @param string|null $customer
     * @return string
     */
    private function getCustomerFolder(?string $customer): string
    {
        if (!$customer) {
            return 'others';
        }

        $customer = strtolower(trim($customer));

        if (strpos($customer, 'astra honda') !== false || strpos($customer, 'ahm') !== false) {
            return 'ahm';
        } elseif (strpos($customer, 'yamaha') !== false || strpos($customer, 'yimm') !== false) {
            return 'yimm';
        }

        return 'others';
    }

    /**
     * Resolve file path by checking subdirectories
     * 
     * @param Item $item
     * @return void
     */
    private function resolveFilePath(Item $item): void
    {
        if (!$item->file_path) {
            return;
        }

        $currentPath = public_path($item->file_path);
        if (file_exists($currentPath)) {
            return;
        }

        // File not found, check subdirectories
        $filename = basename($item->file_path);
        $subfolders = ['ahm', 'yimm', 'others'];

        foreach ($subfolders as $folder) {
            $relativePath = 'master item/' . $folder . '/' . $filename;
            $candidatePath = public_path($relativePath);

            if (file_exists($candidatePath)) {
                $item->file_path = $relativePath;
                $item->save();
                return;
            }
        }

        // Check root folder
        $rootRelative = 'master item/' . $filename;
        $rootPath = public_path($rootRelative);
        if (file_exists($rootPath)) {
            $item->file_path = $rootRelative;
            $item->save();
        }
    }

    /**
     * Process defects from text input
     * 
     * @param string|null $defectsText
     * @return array|null
     */
    private function processDefects(?string $defectsText): ?array
    {
        if (empty($defectsText)) {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode("\n", $defectsText))));
    }

    /**
     * Process dimension standards
     * 
     * @param array $data
     * @return array|null
     */
    private function processDimensionStandards(array $data): ?array
    {
        if (empty($data['dimension_points'])) {
            return null;
        }

        $dimensionStandards = [];
        foreach ($data['dimension_points'] as $key => $point) {
            if (!empty($point)) {
                $dimensionStandards[] = [
                    'point' => $point,
                    'size' => $data['dimension_sizes'][$key] ?? null,
                    'tolerance' => $data['dimension_tolerances'][$key] ?? null,
                    'min' => $data['dimension_mins'][$key] ?? null,
                    'max' => $data['dimension_maxs'][$key] ?? null,
                ];
            }
        }

        return empty($dimensionStandards) ? null : $dimensionStandards;
    }
}
