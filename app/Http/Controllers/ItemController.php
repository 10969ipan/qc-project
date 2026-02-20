<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemService;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index(Request $request)
    {
        $restrictedRoles = ['inspector'];
        // For restricted roles (inspector), override request plant to their own plant
        if (in_array(auth()->user()->role, $restrictedRoles)) {
            $request->merge(['plant' => auth()->user()->plant_id]);
        }

        $filters = [
            'plant' => $request->plant,
            'name' => $request->name,
            'customer' => $request->customer,
            'part_number' => $request->part_number,
            'sap_code' => $request->sap_code,
            'category' => $request->category,
        ];

        $items = $this->itemService->getFilteredItems($filters);

        // Filter categories by plant context
        $plantIdentifier = $request->plant;
        $plantId = null;

        if ($plantIdentifier) {
            $plantId = \App\Models\Plant::resolveId($plantIdentifier);
        } elseif (auth()->user()->plant_id) {
            $plantId = auth()->user()->plant_id;
        }

        $categoriesQuery = \App\Models\Category::orderBy('name');

        if ($plantId) {
            $categoriesQuery->where('plant_id', $plantId);
        }

        $categories = $categoriesQuery->get();
        $plantCode = $plantIdentifier;
        $allPlants = \App\Models\Plant::all();

        return view('items.index', compact('items', 'categories', 'plantCode', 'allPlants'));
    }



    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        $this->itemService->createItem($data);

        // Redirect back to the same plant view
        $queryParams = ['plant' => $data['plant']];

        return redirect()->route('admin.items.index', $queryParams)->with('success', 'Item berhasil ditambahkan.');
    }

    // Menampilkan form edit item
    public function edit(Request $request, Item $item)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        // For admin to edit items from any plant
        if (auth()->user()->role === 'admin') {
            $item = Item::withoutGlobalScope('plant')->findOrFail($item->id);
        }

        // Filter categories by the item's plant (or user's plant if new/fallback)
        $plantId = $item->plant ?? auth()->user()->plant_id; // Using relationship for Item plant ID retrieval if possible, or assume explicit check needed

        // Safety check if $item->plant is object or ID. BelongsTo returns object.
        // But referencing property gives object. $item->plant_id gives ID.
        $targetPlantId = $item->plant_id ?? auth()->user()->plant_id;

        $categoriesQuery = \App\Models\Category::orderBy('name');
        if ($targetPlantId) {
            $categoriesQuery->where('plant_id', $targetPlantId);
        }
        $categories = $categoriesQuery->get();

        if ($request->ajax()) {
            return response()->json([
                'item' => $item->load('plant'),
                'categories' => $categories,
                'defects_text' => $item->defects ? implode("\n", $item->defects) : '',
                'plant_code' => optional($item->plant)->code
            ]);
        }


        return view('items.edit', compact('item', 'categories'));
    }

    // Update data item
    public function update(UpdateItemRequest $request, Item $item)
    {
        $this->itemService->updateItem($item->id, $request->validated());

        // Preserve pagination and filter parameters
        $queryParams = [
            'page' => $request->input('page', 1),
            'name' => $request->input('filter_name'),
            'category' => $request->input('filter_category'),
            'customer' => $request->input('filter_customer'),
            'part_number' => $request->input('filter_part_number'),
            'sap_code' => $request->input('filter_sap_code'),
            'plant' => $request->input('filter_plant'), // Use filter_plant for redirection context
        ];

        // Remove null values
        $queryParams = array_filter($queryParams, function ($value) {
            return !is_null($value) && $value !== '';
        });

        return redirect()->route('admin.items.index', $queryParams)->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $this->itemService->deleteItem($id);

            // Preserve pagination and filter parameters
            $queryParams = [
                'page' => $request->input('page', 1),
                'name' => $request->input('name'),
                'category' => $request->input('category'),
                'customer' => $request->input('customer'),
                'part_number' => $request->input('part_number'),
                'plant' => $request->input('plant'),
            ];

            // Remove null values
            $queryParams = array_filter($queryParams, function ($value) {
                return !is_null($value) && $value !== '';
            });

            return redirect()->route('admin.items.index', $queryParams)->with('success', 'Item berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->back()->with('error', 'Gagal menghapus! Item ini sedang digunakan dalam data laporan (Sub Assy/In Process, dll). Hapus data laporannya terlebih dahulu jika ingin menghapus Item ini.');
            }
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus item: ' . $e->getMessage());
        }
    }

    /**
     * Menyajikan file PDF tertentu berdasarkan index
     */
    public function servePdf($id, $index = 0)
    {
        try {
            $item = Item::withoutGlobalScope('plant')->findOrFail($id);

            // Fetch from file_paths if available, otherwise fallback to legacy file_path
            $filePaths = $item->file_paths;
            $targetPath = null;
            $isLegacy = false;
            $isSimilar = ($index === 'similar');

            if ($isSimilar) {
                $targetPath = $item->similar_part_file_path;
            } elseif (!empty($filePaths) && isset($filePaths[$index])) {
                $targetPath = $filePaths[$index];
            } elseif ($index == 0 && $item->file_path) {
                $targetPath = $item->file_path;
                $isLegacy = true;
            }

            if (!$targetPath) {
                \Log::warning("PDF serve requested for Item ID {$id} type/index {$index} but path is empty.");
                abort(404, 'PDF file path not found');
            }

            // Normalize path separators to forward slashes
            $targetPath = str_replace('\\', '/', $targetPath);
            // Remove leading slashes to ensure clean relative path
            $targetPath = ltrim($targetPath, '/');
            // Remove 'public/' prefix if present to prevent duplication with public_path()
            if (strpos($targetPath, 'public/') === 0) {
                $targetPath = substr($targetPath, 7);
            }

            $filePath = public_path(urldecode($targetPath));

            if (!file_exists($filePath)) {
                // Try to resolve path dynamically (Self-healing with robust search)
                $filename = basename($targetPath);
                // Also consider URL decoded filename if different (e.g. %20 vs space)
                $decodedFilename = urldecode($filename);

                $searchFolders = [
                    'master item/ahm',
                    'master item/yimm',
                    'master item/others',
                    'master item'
                ];

                $foundPath = null;
                $foundRelativePath = null;

                // Helper to find file case-insensitively in a directory
                $findFileInDir = function ($dir, $fname) {
                    if (!is_dir($dir))
                        return null;
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        if ($file === '.' || $file === '..')
                            continue;
                        if (strtolower($file) === strtolower($fname)) {
                            return $file; // Return actual filename found on disk
                        }
                    }
                    return null;
                };

                foreach ($searchFolders as $folderRelative) {
                    $dir = public_path($folderRelative);

                    // Try to find the file (either exact or case-insensitive)
                    // First try raw filename
                    $actualFilename = $findFileInDir($dir, $filename);

                    // If not found, try decoded filename
                    if (!$actualFilename && $filename !== $decodedFilename) {
                        $actualFilename = $findFileInDir($dir, $decodedFilename);
                    }

                    if ($actualFilename) {
                        $foundPath = $dir . DIRECTORY_SEPARATOR . $actualFilename;
                        $foundRelativePath = $folderRelative . '/' . $actualFilename;
                        break;
                    }
                }

                if ($foundPath) {
                    // Update DB with correct path
                    if ($isSimilar) {
                        $item->similar_part_file_path = $foundRelativePath;
                    } elseif ($isLegacy) {
                        $item->file_path = $foundRelativePath;
                        // Also update file_paths if it was empty/syncing
                        if (empty($item->file_paths)) {
                            $item->file_paths = [$foundRelativePath];
                        }
                    } else {
                        // Ensure array initialized
                        if (!is_array($filePaths)) {
                            $filePaths = [];
                        }
                        $filePaths[$index] = $foundRelativePath;
                        $item->file_paths = $filePaths;
                        // Sync legacy file_path if index 0
                        if ($index == 0) {
                            $item->file_path = $foundRelativePath;
                        }
                    }
                    $item->save();

                    $filePath = $foundPath; // Use the found path
                    \Log::info("Resolved missing PDF for Item ID {$id}. Updated path to: {$foundRelativePath}");
                } else {
                    \Log::error("PDF file not found on server for Item ID {$id}. Attempted path: {$filePath}");
                    abort(404, 'PDF file does not exist on server');
                }
            }

            // Sanitize filename for Content-Disposition header (remove quotes to prevent breaking header)
            $safeFilename = str_replace('"', '', basename($filePath));

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $safeFilename . '"'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error serving PDF for Item ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Menghapus PDF tertentu dari item
     */
    public function deletePdf(Request $request, $id, $index)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            $this->itemService->deleteItemPdf($id, $index);
            return response()->json(['success' => true, 'message' => 'PDF berhasil dihapus.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete PDF for Item ID {$id}: " . $e->getMessage());
            // Return a safe error message to avoid JSON encoding issues (e.g. malformed UTF-8 in exception message)
            return response()->json(['success' => false, 'message' => 'Gagal menghapus PDF. Silakan cek log server.'], 500);
        }
    }

    /**
     * Menghapus Similar Part PDF dari item
     */
    public function deleteSimilarPdf(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }
        try {
            $this->itemService->deleteItemSimilarPdf($id);
            return response()->json(['success' => true, 'message' => 'PDF Similar Part berhasil dihapus.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete Similar Part PDF for Item ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus PDF. Silakan cek log server.'], 500);
        }
    }

}
