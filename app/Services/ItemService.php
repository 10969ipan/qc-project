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

        // Admin can switch plants via query parameter
        if (auth()->user()->role === 'admin' && isset($filters['plant'])) {
            $query->withoutGlobalScope('plant')->where('plant', $filters['plant']);
        }

        // Apply search filters
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
            // Handle file upload
            $filePath = null;
            if (isset($data['file'])) {
                $filePath = $this->handleItemFileUpload($data['file'], $data['customer'] ?? null);
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Create item
            $item = Item::create([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'file_path' => $filePath,
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
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
    public function updateItem(int $id, array $data): Item
    {
        DB::beginTransaction();
        try {
            // Get item (with or without global scope based on role)
            if (auth()->user()->role === 'admin') {
                $item = Item::withoutGlobalScope('plant')->findOrFail($id);
            } else {
                $item = Item::findOrFail($id);
            }

            $customerChanged = $item->customer !== ($data['customer'] ?? null);

            // Handle file upload or customer change
            if (isset($data['file'])) {
                // Resolve file path before deletion
                $this->resolveFilePath($item);

                // Delete old file
                $this->deleteFile($item->file_path);

                // Upload new file
                $item->file_path = $this->handleItemFileUpload($data['file'], $data['customer'] ?? null);
            } elseif ($customerChanged && $item->file_path) {
                // Customer changed but no new file - move existing file
                $item->file_path = $this->moveFileToCustomerFolder($item, $data['customer'] ?? null);
            }

            // Process defects
            $defects = $this->processDefects($data['defects'] ?? null);

            // Process dimension standards
            $dimensionStandards = $this->processDimensionStandards($data);

            // Update item
            $item->update([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'customer' => $data['customer'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'sap_code' => $data['sap_code'] ?? null,
                'file_path' => $item->file_path,
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
    public function deleteItem(int $id): bool
    {
        DB::beginTransaction();
        try {
            $query = Item::query();
            if (auth()->user()->role === 'admin') {
                $query->withoutGlobalScope('plant');
            }
            $item = $query->findOrFail($id);

            // Resolve and delete file
            $this->resolveFilePath($item);
            $this->deleteFile($item->file_path);

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
     * Move file to new customer folder
     * 
     * @param Item $item
     * @param string|null $newCustomer
     * @return string
     */
    private function moveFileToCustomerFolder(Item $item, ?string $newCustomer): string
    {
        $this->resolveFilePath($item);

        $oldPath = public_path($item->file_path);

        if (file_exists($oldPath)) {
            $filename = basename($item->file_path);
            $customerFolder = $this->getCustomerFolder($newCustomer);
            $newPath = public_path('master item/' . $customerFolder);

            if (!file_exists($newPath)) {
                mkdir($newPath, 0755, true);
            }

            $newFilePath = $newPath . '/' . $filename;
            rename($oldPath, $newFilePath);

            return 'master item/' . $customerFolder . '/' . $filename;
        }

        return $item->file_path;
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
                ];
            }
        }

        return empty($dimensionStandards) ? null : $dimensionStandards;
    }
}
