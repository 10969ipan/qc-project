<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        // Mulai query untuk model Item
        $query = Item::with('category');

        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('customer') && $request->customer != '') {
            $query->where('customer', 'like', '%' . $request->customer . '%');
        }

        if ($request->has('part_number') && $request->part_number != '') {
            $query->where('part_number', 'like', '%' . $request->part_number . '%');
        }

        if ($request->has('sap_code') && $request->sap_code != '') {
            $query->where('sap_code', 'like', '%' . $request->sap_code . '%');
        }

        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        $items = $query->orderBy('name', 'asc')->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('items.index', compact('items', 'categories'));
    }

    // Menampilkan form pembuatan item
    public function create()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('items.create', compact('categories'));
    }

    // Menyimpan item baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = Item::where('name', $value)
                        ->where('part_number', $request->part_number)
                        ->exists();

                    if ($exists) {
                        $fail('Item dengan nama dan part number yang sama sudah ada.');
                    }
                },
            ],
            'category_id' => 'required|exists:categories,id',
            'file' => 'required|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'sap_code' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    if (!empty($value)) {
                        $exists = Item::where('sap_code', $value)->exists();
                        if ($exists) {
                            $fail('Kode SAP sudah digunakan oleh item lain.');
                        }
                    }
                },
            ],
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Determine customer folder
            $customerFolder = $this->getCustomerFolder($validated['customer']);
            $uploadPath = public_path('master item/' . $customerFolder);

            // Create folder if not exists
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $filePath = 'master item/' . $customerFolder . '/' . $filename;
        }

        $defects = null;
        if ($request->filled('defects')) {
            $defects = array_values(array_filter(array_map('trim', explode("\n", $request->defects))));
        }

        // Proses dimensi dan standar jika diisi
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
            'category_id' => $validated['category_id'],
            'file_path' => $filePath,
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'sap_code' => $validated['sap_code'] ?? null,
            'defects' => $defects,
            'dimension_standards' => $dimension_standards,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Item berhasil ditambahkan.');
    }

    // Menampilkan form edit item
    public function edit(Item $item)
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('items.edit', compact('item', 'categories'));
    }

    // Update data item
    public function update(Request $request, Item $item)
    {
        $itemId = $item->id;
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $itemId) {
                    $exists = Item::where('name', $value)
                        ->where('part_number', $request->part_number)
                        ->where('id', '!=', $itemId)
                        ->exists();

                    if ($exists) {
                        $fail('Item dengan nama dan part number yang sama sudah ada.');
                    }
                },
            ],
            'category_id' => 'required|exists:categories,id',
            'file' => 'nullable|mimes:pdf|max:5120', // Max 5MB
            'customer' => 'nullable|string',
            'part_number' => 'nullable|string',
            'sap_code' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request, $itemId) {
                    if (!empty($value)) {
                        $exists = Item::where('sap_code', $value)
                            ->where('id', '!=', $itemId)
                            ->exists();
                        if ($exists) {
                            $fail('Kode SAP sudah digunakan oleh item lain.');
                        }
                    }
                },
            ],
            'defects' => 'nullable|string',
            'dimension_points' => 'nullable|array',
            'dimension_sizes' => 'nullable|array',
            'dimension_tolerances' => 'nullable|array',
        ]);

        // Cek apakah customer berubah (untuk memindahkan file ke folder yang sesuai)
        $customerChanged = $item->customer !== $validated['customer'];

        if ($request->hasFile('file')) {
            // Resolusi path file untuk memastikan kita menargetkan file yang benar
            $this->resolveFilePath($item);

            // Hapus file lama jika ada
            if ($item->file_path && file_exists(public_path($item->file_path))) {
                unlink(public_path($item->file_path));
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Determine customer folder
            $customerFolder = $this->getCustomerFolder($validated['customer']);
            $uploadPath = public_path('master item/' . $customerFolder);

            // Create folder if not exists
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $item->file_path = 'master item/' . $customerFolder . '/' . $filename;
        } elseif ($customerChanged && $item->file_path) {
            // Customer berubah tapi tidak ada upload file baru - pindahkan file yang ada ke folder customer baru

            // Resolusi path file dulu
            $this->resolveFilePath($item);

            $oldPath = public_path($item->file_path);

            if (file_exists($oldPath)) {
                $filename = basename($item->file_path);
                $customerFolder = $this->getCustomerFolder($validated['customer']);
                $newPath = public_path('master item/' . $customerFolder);

                // Create folder if not exists
                if (!file_exists($newPath)) {
                    mkdir($newPath, 0755, true);
                }

                $newFilePath = $newPath . '/' . $filename;

                // Pindahkan file ke lokasi baru
                rename($oldPath, $newFilePath);
                $item->file_path = 'master item/' . $customerFolder . '/' . $filename;
            }
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
            'category_id' => $validated['category_id'],
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'sap_code' => $validated['sap_code'] ?? null,
            'file_path' => $item->file_path,
            'defects' => $defects,
            'dimension_standards' => $dimension_standards,
        ]);

        // Preserve pagination and filter parameters
        $queryParams = [
            'page' => $request->input('page', 1),
            'name' => $request->input('filter_name'),
            'category' => $request->input('filter_category'),
            'customer' => $request->input('filter_customer'),
            'part_number' => $request->input('filter_part_number'),
        ];

        // Remove null values
        $queryParams = array_filter($queryParams, function ($value) {
            return !is_null($value) && $value !== '';
        });

        return redirect()->route('admin.items.index', $queryParams)->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        // Resolusi path file untuk memastikan lokasi yang benar
        $this->resolveFilePath($item);

        // Hapus file jika ada
        if ($item->file_path && file_exists(public_path($item->file_path))) {
            unlink(public_path($item->file_path));
        }

        $item->delete();

        // Preserve pagination and filter parameters
        $queryParams = [
            'page' => $request->input('page', 1),
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'customer' => $request->input('customer'),
            'part_number' => $request->input('part_number'),
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

            // Coba resolusi path file jika tidak ditemukan di lokasi yang tersimpan
            $this->resolveFilePath($item);

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

    /**
     * Helper untuk menemukan path file aktual, memeriksa subdirektori jika perlu.
     * Mengupdate file_path item jika path yang dikoreksi ditemukan.
     */
    private function resolveFilePath(Item $item)
    {
        if (!$item->file_path) {
            return null;
        }

        $currentPath = public_path($item->file_path);
        if (file_exists($currentPath)) {
            return $currentPath;
        }

        // File tidak ditemukan di path tersimpan, cek subdirektori
        $filename = basename($item->file_path);
        // Subdirektori umum untuk master item
        $subfolders = ['ahm', 'yimm', 'others'];

        foreach ($subfolders as $folder) {
            $relativePath = 'master item/' . $folder . '/' . $filename;
            $candidatePath = public_path($relativePath);

            if (file_exists($candidatePath)) {
                // Found it! Update DB to fix future lookups
                $item->file_path = $relativePath;
                $item->save(); // This persists the change

                return $candidatePath;
            }
        }

        // Juga cek root 'master item' jaga-jaga (misal DB bilang di subfolder tapi aslinya di root)
        $rootRelative = 'master item/' . $filename;
        $rootPath = public_path($rootRelative);
        if (file_exists($rootPath)) {
            $item->file_path = $rootRelative;
            $item->save();
            return $rootPath;
        }

        return null;
    }

    /**
     * Menentukan folder customer berdasarkan nama customer
     */
    private function getCustomerFolder($customer)
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
}
