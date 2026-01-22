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

        return view('items.index', compact('items', 'categories'));
    }

    // Menampilkan form pembuatan item
    public function create(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        // Determine current plant context
        $plantIdentifier = $request->get('plant');
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
        $currentPlant = $request->get('plant', auth()->user()->plant); // Keep for view compatibility if needed, though simpler refactor possible

        return view('items.create', compact('categories', 'currentPlant'));
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
    public function edit(Item $item)
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
            'plant' => $request->input('plant'), // Add plant parameter
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

            if (!empty($filePaths) && isset($filePaths[$index])) {
                $targetPath = $filePaths[$index];
            } elseif ($index == 0 && $item->file_path) {
                $targetPath = $item->file_path;
            }

            if (!$targetPath) {
                \Log::warning("PDF serve requested for Item ID {$id} index {$index} but path is empty.");
                abort(404, 'PDF file path not found');
            }

            $filePath = public_path($targetPath);

            if (!file_exists($filePath)) {
                \Log::error("PDF file not found on server for Item ID {$id}. Attempted path: {$filePath}");
                abort(404, 'PDF file does not exist on server');
            }

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
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
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus PDF: ' . $e->getMessage()], 500);
        }
    }
}
