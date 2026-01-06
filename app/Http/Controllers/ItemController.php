<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

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

        $items = $query->orderBy('name', 'asc')->paginate(10);
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

        // Check if customer has changed
        $customerChanged = $item->customer !== $validated['customer'];

        if ($request->hasFile('file')) {
            // Resolve file path to ensure we're targeting the correct file
            $this->resolveFilePath($item);

            // Delete old file if exists
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
            // Customer changed but no new file uploaded - move existing file to new customer folder

            // Resolve file path first
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

                // Move file to new location
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
            'customer' => $validated['customer'],
            'part_number' => $validated['part_number'] ?? null,
            'file_path' => $item->file_path,
            'defects' => $defects,
            'dimension_standards' => $dimension_standards,
        ]);

        return redirect()->route('admin.items.index')->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Resolve file path to ensure we have the correct location
        $this->resolveFilePath($item);

        // Delete file if exists
        if ($item->file_path && file_exists(public_path($item->file_path))) {
            unlink(public_path($item->file_path));
        }

        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Item berhasil dihapus.');
    }

    /**
     * Serve PDF file
     */
    public function servePdf($id)
    {
        try {
            $item = Item::findOrFail($id);

            if (!$item->file_path) {
                \Log::warning("PDF serve requested for Item ID {$id} but file_path is empty.");
                abort(404, 'PDF file path not stored in database');
            }

            // Try to resolve the file path if it's not found at the stored location
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
     * Helper to find the actual file path, checking subdirectories if necessary.
     * Updates the item's file_path if a corrected path is found.
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

        // File not found at stored path, check subdirectories
        $filename = basename($item->file_path);
        // Common subdirectories for master items
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

        // Also check root 'master item' just in case (e.g. if DB says it is in a subfolder but it is in root)
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
     * Determine the customer folder based on customer name
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
