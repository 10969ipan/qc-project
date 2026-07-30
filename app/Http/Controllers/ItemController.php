<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Plant;
use App\Models\Category;
use App\Services\ItemService;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ItemController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function addCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plant' => 'nullable|string|max:255',
        ]);

        $exists = \App\Models\ItemCustomer::where('name', $request->name)
            ->where(function($q) use ($request) {
                if ($request->plant) {
                    $q->where('plant', $request->plant);
                } else {
                    $q->whereNull('plant');
                }
            })
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Customer sudah ada di daftar!']);
        }

        $customer = \App\Models\ItemCustomer::create([
            'plant' => $request->plant,
            'name' => $request->name,
        ]);

        return response()->json(['success' => true, 'customer' => $customer->name]);
    }

    public function deleteCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'plant' => 'nullable|string',
        ]);

        $query = \App\Models\ItemCustomer::where('name', $request->name);
        if ($request->plant) {
            $query->where('plant', $request->plant);
        } else {
            $query->whereNull('plant');
        }
        $query->delete();

        return response()->json(['success' => true]);
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
            'item_id' => $request->f_item_id ?? $request->item_id,
            'search' => $request->f_search ?? $request->search,
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
        $isTotalView = false;

        if ($plantIdentifier) {
            if ($plantIdentifier === 'total') {
                $isTotalView = true;
                $plantId = \App\Models\Plant::resolveId('total');
            } else {
                // Use exact match for plant code to avoid ambiguous matches
                $plant = \App\Models\Plant::where('code', $plantIdentifier)
                    ->orWhere('id', $plantIdentifier)
                    ->first();
                $plantId = $plant ? $plant->id : null;
                
                if (!$plantId) {
                    $plantId = \App\Models\Plant::resolveId($plantIdentifier);
                }
            }
        } elseif (auth()->user()->plant_id) {
            $plantId = auth()->user()->plant_id;
            $totalPlantId = \App\Models\Plant::where('code', 'total')->value('id');
            if ($plantId === $totalPlantId) {
                $isTotalView = true;
            }
        } else {
            $isTotalView = true;
        }

        $categoriesQuery = Category::with('plant')->orderBy('name');

        // STRICT FILTERING: If we are in a specific plant view, ONLY show that plant's categories.
        // If in total view, show all.
        if (!$isTotalView && $plantId) {
            $categoriesQuery->where('plant_id', $plantId);
        }

        $categories = $categoriesQuery->get();
        $plantCode = $isTotalView ? null : ($plantIdentifier ?: optional(auth()->user()->plant)->code);
        $allPlants = Plant::all();

        // Get all items in a lightweight format for the searchable dropdown
        $allItemsQuery = Item::with('category')->select('id', 'name', 'part_number', 'sap_code', 'customer', 'category_id');
        if ($plantId) {
            $allItemsQuery->where('plant_id', $plantId);
        }
        $allItemsList = $allItemsQuery->orderBy('name')->get();

        $customersQuery = Item::distinct();
        $settingsCustomersQuery = \App\Models\ItemCustomer::query();
        
        if ($plantId) {
            $customersQuery->where('plant_id', $plantId);
            $plantCodeModel = \App\Models\Plant::find($plantId);
            if ($plantCodeModel) {
                $settingsCustomersQuery->where('plant', $plantCodeModel->code);
            }
        }
        
        $itemCustomers = $customersQuery->pluck('customer')->filter()->values()->toArray();
        $settingCustomers = $settingsCustomersQuery->pluck('name')->filter()->values()->toArray();
        
        $customers = collect(array_merge($itemCustomers, $settingCustomers))->unique()->sort()->values();

        return view('items.index', compact('items', 'categories', 'plantCode', 'allPlants', 'allItemsList', 'customers'));
    }



    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        $item = $this->itemService->createItem($data);
        ActivityLogger::log('created', $item, "Menambahkan item baru: {$item->name}");

        // Redirect back to the same plant view with filters and pagination
        $queryParams = [
            'plant' => $data['plant'],
            'page' => $request->input('page'),
            'f_item_id' => $request->input('f_item_id'),
            'f_search' => $request->input('f_search'),
            'name' => $request->input('filter_name'),
            'category' => $request->input('filter_category'),
            'customer' => $request->input('filter_customer'),
            'part_number' => $request->input('filter_part_number'),
            'sap_code' => $request->input('filter_sap_code'),
        ];

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
            'f_item_id' => $request->input('f_item_id'),
            'f_search' => $request->input('f_search'),
            'name' => $request->input('filter_name'),
            'category' => $request->input('filter_category'),
            'customer' => $request->input('filter_customer'),
            'part_number' => $request->input('filter_part_number'),
            'sap_code' => $request->input('filter_sap_code'),
            'plant' => $request->input('filter_plant'), 
        ];

        // Remove null values
        $queryParams = array_filter($queryParams, function ($value) {
            return !is_null($value) && $value !== '';
        });

        $item = Item::withoutGlobalScope('plant')->find($item->id);
        ActivityLogger::log('updated', $item, "Memperbarui data item: {$item->name}");

        return redirect()->route('admin.items.index', $queryParams)->with('success', 'Item berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        try {
            $item = Item::withoutGlobalScope('plant')->find($id);
            $itemName = $item ? $item->name : 'Unknown';
            $this->itemService->deleteItem($id);
            ActivityLogger::log('deleted', null, "Menghapus item: {$itemName}");

            // Preserve pagination and filter parameters
            $queryParams = [
                'page' => $request->input('page', 1),
                'f_item_id' => $request->input('f_item_id'),
                'f_search' => $request->input('f_search'),
                'name' => $request->input('name'),
                'category' => $request->input('category'),
                'customer' => $request->input('customer'),
                'part_number' => $request->input('part_number'),
                'sap_code' => $request->input('sap_code'),
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

            // Check if download is requested
            if (request()->has('download')) {
                return response()->download($filePath, $safeFilename);
            }

            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
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
            $item = Item::withoutGlobalScope('plant')->find($id);
            $urutan = $index + 1;
            ActivityLogger::log('deleted', $item, "Menghapus file PDF Standard (lampiran ke-{$urutan}) pada item: {$item->name}");
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
            $item = Item::withoutGlobalScope('plant')->find($id);
            ActivityLogger::log('deleted', $item, "Menghapus Similar Part PDF pada item: {$item->name}");
            return response()->json(['success' => true, 'message' => 'PDF Similar Part berhasil dihapus.']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete Similar Part PDF for Item ID {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus PDF. Silakan cek log server.'], 500);
        }
    }

    /**
     * Search for an item by its part number.
     * Used for QR code auto-fill functionality.
     */
    public function searchByPartNumber(Request $request)
    {
        $partNumberInput = $request->query('part_number');
        $sapCodeInput = $request->query('sap_code');

        if (!$partNumberInput && !$sapCodeInput) {
            return response()->json(['success' => false, 'message' => 'Pencarian gagal: Data QR tidak terbaca dengan benar.'], 400);
        }

        // Fungsi pembantu untuk normalisasi (hapus semua karakter non-alfanumerik)
        $normalize = function ($str) {
            return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $str ?? ''));
        };

        $normalizedInput = $normalize($partNumberInput);
        $item = null;

        // 1. Prioritas Utama: Cari Berdasarkan Nama Item (menggunakan part number input sebagai keyword)
        if ($partNumberInput) {
            $item = Item::where('name', 'LIKE', '%' . $partNumberInput . '%')
                ->orWhere('name', 'LIKE', '%' . $normalizedInput . '%')
                ->first();
        }

        // 2. Prioritas Kedua: Cari Berdasarkan Part Number (Normalized)
        if (!$item && $normalizedInput) {
            // Kita ambil dulu candidate yang mirip untuk efisiensi
            $candidates = Item::where('part_number', 'LIKE', '%' . substr($normalizedInput, 0, 3) . '%')->get();
            foreach ($candidates as $candidate) {
                if ($normalize($candidate->part_number) === $normalizedInput) {
                    $item = $candidate;
                    break;
                }
            }
        }

        // 3. Prioritas Terakhir: Cari Berdasarkan SAP Code
        if (!$item && $sapCodeInput) {
            $item = Item::where('sap_code', $sapCodeInput)->first();
            
            // Fallback: Jika tidak ketemu, coba normalisasi SAP Code
            if (!$item) {
                $normalizedSap = $normalize($sapCodeInput);
                $candidates = Item::where('sap_code', 'LIKE', '%' . substr($normalizedSap, 0, 3) . '%')->get();
                foreach ($candidates as $candidate) {
                    if ($normalize($candidate->sap_code) === $normalizedSap) {
                        $item = $candidate;
                        break;
                    }
                }
            }
        }

        if (!$item) {
            $msg = "Item tidak ditemukan.\n";
            $msg .= "Part: " . ($partNumberInput ?: '-') . "\n";
            $msg .= "SAP: " . ($sapCodeInput ?: '-');
            return response()->json(['success' => false, 'message' => $msg], 404);
        }

        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    /**
     * Check if a QR code has already been used in any checksheet table.
     * Validasi menggunakan composite 5-field dari seluruh segmen raw barcode:
     * part_code | supplier_id | quantity | unique_code_id | sap_code
     */
    public function checkQrUniqueness(Request $request)
    {
        $qrCode = $request->query('qrcode');

        if (!$qrCode) {
            return response()->json(['success' => false, 'message' => 'QR data is empty.'], 400);
        }

        // Validasi format: harus 5 segmen dipisah '|'
        if (strpos($qrCode, '|') === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format QR tidak valid. Harus berformat: part|supplier|qty|unique_id|sap'
            ], 400);
        }

        $parts = explode('|', $qrCode);
        $count = count($parts);

        if ($count === 6 && strpos(trim($parts[5]), 'CBT-') === 0) {
            $partCode     = trim($parts[0]);
            $supplierId   = trim($parts[1]);
            $quantity     = trim($parts[4]); // qty split bucket
            $lotCabut     = trim($parts[3]);
            $uniqueCodeId = $lotCabut . '|' . trim($parts[5]);
            $sapCode      = $partCode;
        } elseif ($count >= 5) {
            $partCode     = trim($parts[0]);
            $supplierId   = trim($parts[1]);
            $quantity     = trim($parts[2]);
            $uniqueCodeId = implode('|', array_slice($parts, 3, $count - 4));
            $sapCode      = trim($parts[$count - 1]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Format QR tidak valid. Minimal 5 segmen dibutuhkan.'
            ], 400);
        }

        $tables = [
            'incoming_parts'          => 'Incoming Part',
            'in_process_checksheets'  => 'In-Process',
            'sub_assy_checksheets'    => 'Sub Assy',
            'plating_checksheets'     => 'Plating',
            'double_tape_checksheets' => 'Double Tape',
            'painting_checksheets'    => 'Painting',
            'incoming_exports'        => 'Incoming Export',
        ];

        foreach ($tables as $table => $moduleName) {
            // Pengecekan composite: seluruh segmen raw barcode harus unik
            // KHUSUS untuk In-Process: hanya mengecek qty dan lot_id-unique_code-cav (yaitu quantity dan unique_code_id)
            if (!empty($uniqueCodeId)) {
                $query = DB::table($table)->where('unique_code_id', $uniqueCodeId);
                if (!empty($sapCode)) {
                    $query->where('sap_code', $sapCode);
                }
                $record = $query->latest()->first();
            } else {
                $record = DB::table($table)
                    ->where('part_code',     $partCode)
                    ->where('supplier_id',   $supplierId)
                    ->where('quantity',      $quantity)
                    ->where('sap_code',      $sapCode)
                    ->latest()
                    ->first();
            }

            if ($record) {
                $date = Carbon::parse($record->created_at)->format('d-m-Y H:i');

                if ($table === 'in_process_checksheets') {
                    return response()->json([
                        'success' => true,
                        'unique'  => false,
                        'message' => "QR ini sudah pernah diinput pada {$date} di modul {$moduleName}. "
                            . "(Qty: {$quantity} | ID: {$uniqueCodeId} | SAP: {$sapCode})"
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'unique'  => false,
                    'message' => "QR ini sudah pernah diinput pada {$date} di modul {$moduleName}. "
                        . "(Part: {$partCode} | Supplier: {$supplierId} | Qty: {$quantity} | ID: {$uniqueCodeId} | SAP: {$sapCode})"
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'unique'  => true,
        ]);
    }

    /**
     * Download template Excel untuk import master data item.
     */
    public function downloadTemplate(Request $request)
    {
        // Check authorization
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift'])) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan tindakan ini.');
        }

        $plantIdentifier = $request->query('plant');
        $plantId = null;
        if ($plantIdentifier && $plantIdentifier !== 'total') {
            $plantId = Plant::resolveId($plantIdentifier);
        } else if (auth()->user()->plant_id) {
            $plantId = auth()->user()->plant_id;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Master Data Item');

        $headers = [
            'Nama Item',
            'Kategori',
            'Customer',
            'Nomor Part',
            'Kode SAP',
            'Cavity',
            'Standar Berat',
            'SCT Plating',
            'Defects'
        ];

        // Tulis header
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            
            // Format header: Bold, text centered, and light gray background
            $style = $sheet->getStyle($colLetter . '1');
            $style->getFont()->setBold(true);
            $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $style->getFill()->getStartColor()->setARGB('FFE2E8F0');
            $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Ambil kategori untuk dropdown opsi
        $categoriesQuery = Category::withoutGlobalScope('plant');
        if ($plantId) {
            $categoriesQuery->where('plant_id', $plantId);
        }
        $categoriesList = $categoriesQuery->pluck('name')->unique()->toArray();
        $categoryNames = array_map('strtoupper', array_filter(array_map('trim', $categoriesList)));
        if (empty($categoryNames)) {
            $categoryNames = ['IN PROCESS', 'PLATING'];
        }
        $formula = '"' . implode(',', $categoryNames) . '"';

        // Set validation dropdown pada kolom B (Kategori) baris 2-1000
        for ($r = 2; $r <= 1000; $r++) {
            $validation = $sheet->getCell('B' . $r)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input Salah');
            $validation->setError('Pilih Kategori dari daftar opsi yang disediakan.');
            $validation->setPromptTitle('Kategori');
            $validation->setPrompt('Silakan pilih Kategori.');
            $validation->setFormula1($formula);
        }

        // Ambil data item yang sudah ada untuk diexport/download
        $itemsQuery = Item::withoutGlobalScope('plant')->with('category');
        if ($plantId) {
            $itemsQuery->where('plant_id', $plantId);
        }
        $items = $itemsQuery->get();

        $rowsData = [];
        if ($items->count() > 0) {
            foreach ($items as $item) {
                $categoryName = $item->category ? $item->category->name : '';
                $defectsString = '';
                if ($item->defects && is_array($item->defects)) {
                    $defectsString = implode(', ', $item->defects);
                }
                $rowsData[] = [
                    $item->name,
                    $categoryName,
                    $item->customer,
                    $item->part_number,
                    $item->sap_code,
                    $item->cavity,
                    $item->weight_standard,
                    $item->standard_cycle_time,
                    $defectsString
                ];
            }
        } else {
            // Default sample rows if no items exist
            $rowsData = [
                [
                    'GRILL RAD C GR',
                    'IN PROCESS',
                    'AHM',
                    '64301-K59-A70',
                    '12345678',
                    2,
                    '150.5',
                    '',
                    'Scratch, Bintik, Silver, Belang'
                ],
                [
                    'FRONT TOP COVER',
                    'PLATING',
                    'YIMM',
                    '2DP-F835U-00',
                    '87654321',
                    1,
                    '85.2',
                    0.15,
                    'Meler, kotor, kasar'
                ]
            ];
        }

        foreach ($rowsData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $val) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($colLetter . ($rowIndex + 2), $val);
            }
        }

        // Auto size columns
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_master_data_item.xlsx';

        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Bulk upload PDF ke semua item dalam satu kategori.
     * File PDF yang diupload akan menggantikan file_paths[0] (PCCP standard) semua item di kategori tersebut.
     */
    public function bulkUploadPdf(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action. Fitur ini hanya untuk Admin.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized action. Fitur ini hanya untuk Admin.');
        }

        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'pdf_file'     => 'required|file|mimes:pdf|max:10240',
            'pdf_type'     => 'required|in:standard,similar',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'pdf_file.required'    => 'File PDF wajib diunggah.',
            'pdf_file.mimes'       => 'File harus berformat PDF.',
            'pdf_file.max'         => 'Ukuran file tidak boleh melebihi 10MB.',
            'pdf_type.required'    => 'Tipe PDF wajib dipilih.',
        ]);

        $categoryId = $request->category_id;
        $pdfType    = $request->pdf_type; // 'standard' or 'similar'

        $items = Item::withoutGlobalScope('plant')
            ->where('category_id', $categoryId)
            ->get();

        if ($items->isEmpty()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada item dalam kategori ini.'], 400);
            }
            return redirect()->back()->with('error', 'Tidak ada item dalam kategori ini.');
        }

        $file           = $request->file('pdf_file');
        $originalName   = $file->getClientOriginalName();
        $updatedCount   = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($items as $item) {
                // Upload copy for each item (shared filename with item id prefix to avoid collision)
                $filename     = time() . '_' . $item->id . '_' . $originalName;
                $customerFolder = $this->resolveCustomerFolder($item->customer);
                $relativePath  = 'master item/' . $customerFolder . '/' . $filename;
                $uploadDir     = public_path('master item/' . $customerFolder);

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Copy source file (not move, so we can reuse for next item)
                copy($file->getRealPath(), $uploadDir . '/' . $filename);

                if ($pdfType === 'standard') {
                    // Delete old first standard PDF file
                    $existingPaths = $item->file_paths ?? [];
                    if (!empty($existingPaths[0])) {
                        $oldPath = public_path($existingPaths[0]);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    // Replace only index 0; keep any additional files
                    $existingPaths[0] = $relativePath;
                    $item->update([
                        'file_paths' => array_values($existingPaths),
                        'file_path'  => $relativePath,
                    ]);
                } else {
                    // similar/dimensi
                    if ($item->similar_part_file_path) {
                        $oldPath = public_path($item->similar_part_file_path);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $item->update(['similar_part_file_path' => $relativePath]);
                }

                $updatedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            $category = \App\Models\Category::find($categoryId);
            $catName  = $category ? $category->name : $categoryId;
            ActivityLogger::log('updated', null, "Bulk upload PDF ({$pdfType}) untuk kategori '{$catName}': {$updatedCount} item diperbarui.");

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => "Berhasil mengganti PDF untuk {$updatedCount} item di kategori '{$catName}'."]);
            }
            return redirect()->back()->with('success', "Berhasil mengganti PDF untuk {$updatedCount} item di kategori '{$catName}'.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal bulk upload PDF: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal bulk upload PDF: ' . $e->getMessage());
        }
    }

    /**
     * Resolve customer folder name for file storage.
     */
    private function resolveCustomerFolder(?string $customer): string
    {
        if (!$customer) return 'others';
        $c = strtolower(trim($customer));
        if (str_contains($c, 'astra honda') || str_contains($c, 'ahm')) return 'ahm';
        if (str_contains($c, 'yamaha') || str_contains($c, 'yimm')) return 'yimm';
        return 'others';
    }

    /**
     * Import master data item dari file Excel/CSV.
     */
    public function import(Request $request)
    {
        // Check authorization
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'supervisor', 'kashift'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan tindakan ini.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'plant' => 'required|string',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file tidak boleh melebihi 5MB.',
            'plant.required' => 'Plant wajib dipilih.',
        ]);

        $plantId = Plant::resolveId($request->plant);
        if (!$plantId) {
            return redirect()->back()->with('error', 'Plant tidak valid.');
        }

        $plantModel = Plant::find($plantId);
        $plantCode = $plantModel ? $plantModel->code : $request->plant;

        $file = $request->file('file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (count($rows) <= 1) {
            return redirect()->back()->with('error', 'File Excel kosong atau hanya berisi baris header.');
        }

        $insertedCount = 0;
        $updatedCount = 0;
        $warnings = [];
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Lewati baris header pertama
                if ($index === 0) {
                    continue;
                }

                // Cek baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                $name = isset($row[0]) ? trim((string)$row[0]) : '';
                $categoryName = isset($row[1]) ? trim((string)$row[1]) : '';
                $customer = isset($row[2]) ? trim((string)$row[2]) : null;
                $partNumber = isset($row[3]) ? trim((string)$row[3]) : null;
                $sapCode = isset($row[4]) ? trim((string)$row[4]) : null;
                $cavity = isset($row[5]) ? intval($row[5]) : 1;
                $weightStandard = isset($row[6]) ? trim((string)$row[6]) : null;
                $sctPlating = isset($row[7]) && is_numeric($row[7]) ? floatval($row[7]) : null;
                $defectsText = isset($row[8]) ? trim((string)$row[8]) : '';

                // Validasi data minimal
                if (empty($name)) {
                    $warnings[] = "Baris " . ($index + 1) . ": Nama Item kosong, dilewati.";
                    $skippedCount++;
                    continue;
                }

                if (empty($categoryName)) {
                    $warnings[] = "Baris " . ($index + 1) . ": Kategori kosong untuk item '{$name}', dilewati.";
                    $skippedCount++;
                    continue;
                }

                // Cari atau buat kategori baru
                $category = Category::withoutGlobalScope('plant')
                    ->where('plant_id', $plantId)
                    ->where(DB::raw('LOWER(name)'), strtolower($categoryName))
                    ->first();

                if (!$category) {
                    $category = Category::create([
                        'plant_id' => $plantId,
                        'name' => strtoupper($categoryName)
                    ]);
                }

                // Normalisasi defects
                $defects = null;
                if (!empty($defectsText)) {
                    $defectsList = array_values(array_filter(array_map('trim', preg_split('/[,|;]/', $defectsText))));
                    if (!empty($defectsList)) {
                        $defects = $defectsList;
                    }
                }

                if ($cavity <= 0) {
                    $cavity = 1;
                }

                // Cari item yang sudah ada untuk di-update
                $existingItem = null;

                // Match 1: Berdasarkan sap_code di plant & kategori yang sama
                if (!empty($sapCode)) {
                    $existingItem = Item::withoutGlobalScope('plant')
                        ->where('plant_id', $plantId)
                        ->where('category_id', $category->id)
                        ->where('sap_code', $sapCode)
                        ->first();
                }

                // Match 2: Berdasarkan part_number di plant & kategori yang sama
                if (!$existingItem && !empty($partNumber)) {
                    $existingItem = Item::withoutGlobalScope('plant')
                        ->where('plant_id', $plantId)
                        ->where('category_id', $category->id)
                        ->where('part_number', $partNumber)
                        ->first();
                }

                // Match 3: Berdasarkan name di plant & kategori yang sama
                if (!$existingItem) {
                    $existingItem = Item::withoutGlobalScope('plant')
                        ->where('plant_id', $plantId)
                        ->where('category_id', $category->id)
                        ->where('name', $name)
                        ->first();
                }

                $itemData = [
                    'plant_id' => $plantId,
                    'name' => $name,
                    'category_id' => $category->id,
                    'customer' => $customer,
                    'part_number' => $partNumber,
                    'sap_code' => $sapCode,
                    'cavity' => $cavity,
                    'weight_standard' => $weightStandard,
                    'standard_cycle_time' => $sctPlating,
                    'defects' => $defects,
                ];

                if ($existingItem) {
                    // Update
                    $existingItem->update($itemData);
                    $updatedCount++;
                } else {
                    // Insert
                    Item::create($itemData);
                    $insertedCount++;
                }
            }

            DB::commit();

            ActivityLogger::log('updated', null, "Melakukan import master data item untuk plant {$plantCode} ({$insertedCount} baru, {$updatedCount} diperbarui, {$skippedCount} dilewati)");

            $successMsg = "Berhasil memproses import: {$insertedCount} item baru ditambahkan, {$updatedCount} item diperbarui.";
            if ($skippedCount > 0) {
                $successMsg .= " {$skippedCount} item dilewati.";
            }

            if (!empty($warnings)) {
                return redirect()->back()->with('success', $successMsg)->with('import_warnings', $warnings);
            }

            return redirect()->back()->with('success', $successMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses data import: ' . $e->getMessage());
        }
    }
}
