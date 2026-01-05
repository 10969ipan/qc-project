<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    private $partDimensionStandards = [
        '53102-K0L -D002' => [ // Corresponds to "COVER HNDL END K3VA"
            '1' => ['size' => 5, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.5],
            '4' => ['size' => 20.5, 'tolerance' => 0.2],
            '5' => ['size' => 20, 'tolerance' => 0.2],
        ],
        '1PA - F836B - 00' => [ // Corresponds to "EMBLEM 3D"
            '1' => ['size' => 25, 'tolerance' => 0.2],
            '2' => ['size' => 21, 'tolerance' => 0.4],
            '3' => ['size' => 3.2, 'tolerance' => 0.2],
            '4' => ['size' => 24, 'tolerance' => 0.4],
        ],
        '53209-K3V-N100' => [ // Corresponds to "COVER HEAD LIGHT (NATURAL)"
            '1' => ['size' => 10, 'tolerance' => 0.2],
            '2' => ['size' => 10, 'tolerance' => 0.2],
            '3' => ['size' => 10, 'tolerance' => 0.2],
            '4' => ['size' => 10, 'tolerance' => 0.2],
        ],
    ];

    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('customer') && $request->customer != '') {
            $query->where('customer', 'like', '%' . $request->customer . '%');
        }

        if ($request->has('part_number') && $request->part_number != '') {
            $query->where('part_number', 'like', '%' . $request->part_number . '%');
        }

        $items = $query->paginate(10);
        return view('admin.items.index', compact('items'));
    }

    public function create()
    {
        $partDimensionStandards = $this->partDimensionStandards;
        return view('admin.items.create', compact('partDimensionStandards'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Use Storage facade with public disk
            $filePath = $file->storeAs('items_files', $filename, 'public');
        }

        $defects = null;
        if ($request->filled('defects')) {
            $defects = array_values(array_filter(array_map('trim', explode("\n", $request->defects))));
        }

        $dimension_standards = null;
        if ($request->filled('dimension_points')) {
            $dimension_standards = [];
            foreach ($request->dimension_points as $key => $point) {
                if (!empty($point)) {
                    $dimension_standards[] = [
                        'point' => $point,
                        'size' => $request->dimension_sizes[$key] ?? null,
                        'tolerance' => $request->dimension_tolerances[$key] ?? null,
                    ];
                }
            }
        }

        Item::create([
            'name' => $validated['name'],
            'file_path' => $filePath,
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'defects' => $defects,
            'dimension_standards' => $dimension_standards,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $partDimensionStandards = $this->partDimensionStandards;
        return view('admin.items.edit', compact('item', 'partDimensionStandards'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
                Storage::disk('public')->delete($item->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Use Storage facade with public disk
            $item->file_path = $file->storeAs('items_files', $filename, 'public');
        }

        $defects = null;
        if ($request->filled('defects')) {
            $defects = array_values(array_filter(array_map('trim', explode("\n", $request->defects))));
        }

        $dimension_standards = null;
        if ($request->filled('dimension_points')) {
            $dimension_standards = [];
            foreach ($request->dimension_points as $key => $point) {
                if (!empty($point)) {
                    $dimension_standards[] = [
                        'point' => $point,
                        'size' => $request->dimension_sizes[$key] ?? null,
                        'tolerance' => $request->dimension_tolerances[$key] ?? null,
                    ];
                }
            }
        }

        $item->update([
            'name' => $validated['name'],
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'file_path' => $item->file_path,
            'defects' => $defects,
            'dimension_standards' => $dimension_standards,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->file_path && Storage::disk('public')->exists($item->file_path)) {
            Storage::disk('public')->delete($item->file_path);
        }
        $item->delete();
        return redirect()->route('admin.items.index')->with('success', 'Item berhasil dihapus.');
    }
}
