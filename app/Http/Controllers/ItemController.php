<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
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

        $items = $query->get();
        return view('admin.items.index', compact('items'));
    }

    public function create()
    {
        return view('admin.items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'defects' => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('items_files'), $filename);
            $filePath = 'items_files/' . $filename;
        }

        $defects = null;
        if ($request->filled('defects')) {
            $defects = array_values(array_filter(array_map('trim', explode("\n", $request->defects))));
        }

        Item::create([
            'name' => $validated['name'],
            'file_path' => $filePath,
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'defects' => $defects,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        return view('admin.items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'defects' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_path && file_exists(public_path($item->file_path))) {
                unlink(public_path($item->file_path));
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('items_files'), $filename);
            $item->file_path = 'items_files/' . $filename;
        }

        $defects = null;
        if ($request->filled('defects')) {
            $defects = array_values(array_filter(array_map('trim', explode("\n", $request->defects))));
        }

        $item->update([
            'name' => $validated['name'],
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'file_path' => $item->file_path,
            'defects' => $defects,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        if ($item->file_path && file_exists(public_path($item->file_path))) {
            unlink(public_path($item->file_path));
        }
        $item->delete();
        return redirect()->route('admin.items.index')->with('success', 'Barang berhasil dihapus.');
    }
}
