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
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('items.index', compact('items', 'categories'));
    }

    // Menampilkan form pembuatan item
    public function create(Request $request)
    {
        $categories = \App\Models\Category::orderBy('name')->get();

        // Determine current plant context
        $currentPlant = $request->get('plant', auth()->user()->plant);

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
        // For admin to edit items from any plant
        if (auth()->user()->role === 'admin') {
            $item = Item::withoutGlobalScope('plant')->findOrFail($item->id);
        }

        $categories = \App\Models\Category::orderBy('name')->get();
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
    }

    /**
     * Menyajikan file PDF
     */
    public function servePdf($id)
    {
        try {
            $item = Item::findOrFail($id);

            if (!$item->file_path) {
                \Log::warning("PDF serve requested for Item ID {$id} but file_path is empty.");
                abort(404, 'PDF file path not stored in database');
            }

            $filePath = public_path($item->file_path);

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
}
