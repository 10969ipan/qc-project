<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Helpers\ShiftHelper;
use App\Models\AppMenu;
use App\Models\Category;
use App\Models\DandoryChecksheet;
use App\Models\DandoryItem;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Services\DandoryChecksheetService;

class DandoryChecksheetController extends Controller
{
    protected DandoryChecksheetService $service;

    public function __construct(DandoryChecksheetService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Utama Checksheet Dandory Quality
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));

        // 1. Filter Tanggal Kalender (Default: Hari ini / Production date berdasarkan jam kerja)
        $date        = $request->get('date', ShiftHelper::getProductionDate());
        $dateCarbon  = Carbon::parse($date);
        $month       = (int) $dateCarbon->format('n');
        $year        = (int) $dateCarbon->format('Y');

        // 2. Filter Shift (Default: Shift yang sedang aktif sesuai jam saat ini: Shift 1 / 2 / 3)
        $defaultShift = 'Shift ' . ShiftHelper::getShift();
        if ($request->has('shift')) {
            $shiftFilter = $request->get('shift'); // bisa "" (Semua Shift) atau "Shift 1", dll
        } else {
            $shiftFilter = $defaultShift;
        }

        $tableFilter = $request->get('table_no');

        // ponytail: check menu view permission
        if ($currentUser->role !== 'admin') {
            $menu = AppMenu::where('name', 'Dandory Quality')->where('plant_code', $plantCode)->first()
                ?? AppMenu::where('name', 'Dandory Quality')->first();

            if ($menu && !$currentUser->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki akses ke menu Checksheet Dandory Quality.');
            }
        }

        // Cek apakah user yang login adalah inspector/operator
        $isInspector = in_array($currentUser->role, ['operator', 'inspector']);

        if ($isInspector) {
            // Inspector: hanya menampilkan data dirinya sendiri, tidak bisa melihat data user lain
            $selectedOperatorId = $currentUser->id;
            $inspectors = collect([$currentUser]);
        } else {
            // Karu, kashift ke atas: jika filter operator dipilih gunakan itu; jika default, tampilkan data yang baru saja di-update
            $inspectors = $this->service->getPlantOperators($plantCode);
            $selectedOperatorId = (int) $request->get('operator_id');

            if (!$selectedOperatorId) {
                // Cari checksheet yang paling baru di-update di tanggal, shift & plant ini
                $latestQuery = DandoryChecksheet::where('plant_code', $plantCode)
                    ->whereDate('check_date', $date);

                if (!empty($shiftFilter)) {
                    $latestQuery->where('shift', $shiftFilter);
                }

                $latestDandory = $latestQuery->latest('updated_at')->first();

                if ($latestDandory) {
                    $selectedOperatorId = $latestDandory->operator_id;
                } else {
                    $selectedOperatorId = $inspectors->first()?->id ?? $currentUser->id;
                }
            }
        }

        $selectedOperator = User::find($selectedOperatorId) ?? $currentUser;

        // Pastikan selectedOperator ada di koleksi dropdown inspectors
        if ($selectedOperator && !$inspectors->contains('id', $selectedOperator->id)) {
            $inspectors->push($selectedOperator);
        }

        // Ambil konfigurasi posisi/bagian operator & kategori part
        $opConfig = $this->service->getOperatorConfig($plantCode, $selectedOperatorId);

        // Ambil daftar meja per plant
        $plantTables = $this->service->getPlantTables($plantCode);

        // Ambil master item parameter standar (Awal & Akhir)
        $masterItems = $this->service->getMasterItems();
        $awalItems   = $masterItems->where('phase', 'awal')->values();
        $akhirItems  = $masterItems->where('phase', 'akhir')->values();

        // Ambil daftar checksheet transaksi (Hanya per tanggal & per shift)
        $filters = [
            'plant_code'  => $plantCode,
            'operator_id' => $selectedOperatorId,
            'date'        => $date,
            'shift'       => $shiftFilter,
        ];
        $checksheets = $this->service->getFilteredChecksheets($filters, 15);

        // Pre-compute row metrics (O(N) in controller, no N+1 in blade)
        $awalTotal = count($awalItems) ?: 9;
        $akhirTotal = count($akhirItems) ?: 10;

        foreach ($checksheets as $cs) {
            $awalCount = count((array) ($cs->awal_checks ?? []));
            $akhirCount = count((array) ($cs->akhir_checks ?? []));

            $cs->is_awal_done = $awalTotal > 0 && $awalCount >= $awalTotal;
            $cs->is_akhir_done = $akhirTotal > 0 && $akhirCount >= $akhirTotal;
            $cs->is_complete = $cs->is_awal_done && $cs->is_akhir_done;

            if ($cs->leader_checked) {
                $cs->status_badge = 'badge-success';
                $cs->status_text  = 'Terverifikasi';
            } elseif ($cs->is_complete) {
                $cs->status_badge = 'badge-primary';
                $cs->status_text  = 'Lengkap (Siap Approval)';
            } elseif ($cs->is_akhir_done) {
                $cs->status_badge = 'badge-primary';
                $cs->status_text  = 'Akhir Selesai';
            } else {
                $cs->status_badge = 'badge-warning text-dark';
                $cs->status_text  = 'Proses (Awal)';
            }
        }

        // Ambil part yang diizinkan untuk operator ini
        $availableItems = $this->service->getItemsForOperator($plantCode, $selectedOperatorId);

        // Ambil kategori master (untuk modal kelola operator)
        $categories = Category::orderBy('name')->get(['id', 'name']);

        // Ambil mapping konfigurasi semua operator di plant ini & kategori terbaru per bagian untuk auto-inheritance
        $opConfigsRaw = \App\Models\DandoryOperatorConfig::where('plant_code', strtolower($plantCode))
            ->orderBy('updated_at', 'desc')
            ->get();

        $allOpConfigs = [];
        $latestBagianCategories = [];

        foreach ($opConfigsRaw as $cfg) {
            $cats = is_array($cfg->allowed_categories) ? array_values(array_map('strval', $cfg->allowed_categories)) : [];
            $allOpConfigs[$cfg->operator_id] = [
                'bagian'             => $cfg->bagian,
                'allowed_categories' => $cats,
            ];

            if (!empty($cfg->bagian) && !empty($cats) && !isset($latestBagianCategories[$cfg->bagian])) {
                $latestBagianCategories[$cfg->bagian] = $cats;
            }
        }

        foreach ($inspectors as $insp) {
            if (!isset($allOpConfigs[$insp->id])) {
                $compBagian = $this->service->getOperatorBagianFromCompliance($insp->id);
                $inheritedCats = $latestBagianCategories[$compBagian] ?? [];
                $allOpConfigs[$insp->id] = [
                    'bagian'             => $compBagian,
                    'allowed_categories' => $inheritedCats,
                ];
            }
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('dandory.index', compact(
            'plantCode', 'date', 'month', 'year', 'monthNames', 'shiftFilter', 'defaultShift', 'tableFilter',
            'inspectors', 'selectedOperatorId', 'selectedOperator', 'opConfig',
            'plantTables', 'awalItems', 'akhirItems', 'checksheets', 'availableItems', 'categories',
            'allOpConfigs', 'latestBagianCategories'
        ));
    }

    /**
     * Tampilan Cetak / Print PDF (A4 Landscape Standar ISO QC-KRW-F-0094)
     */
    public function print(Request $request)
    {
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $date        = $request->get('date', ShiftHelper::getProductionDate());
        $dateCarbon  = Carbon::parse($date);
        $month       = (int) $request->get('month', $dateCarbon->format('n'));
        $year        = (int) $request->get('year', $dateCarbon->format('Y'));
        $shiftFilter = $request->get('shift');
        $tableFilter = $request->get('table_no');

        $isInspector = in_array($currentUser->role, ['operator', 'inspector']);

        if ($isInspector) {
            $selectedOperatorId = $currentUser->id;
        } else {
            $inspectors = $this->service->getPlantOperators($plantCode);
            $selectedOperatorId = (int) $request->get('operator_id');

            if (!$selectedOperatorId) {
                $latestQuery = DandoryChecksheet::where('plant_code', $plantCode)
                    ->whereDate('check_date', $date);

                if (!empty($shiftFilter)) {
                    $latestQuery->where('shift', $shiftFilter);
                }

                $latestDandory = $latestQuery->latest('updated_at')->first();

                if ($latestDandory) {
                    $selectedOperatorId = $latestDandory->operator_id;
                } else {
                    $selectedOperatorId = $inspectors->first()?->id ?? $currentUser->id;
                }
            }
        }

        $selectedOperator   = User::find($selectedOperatorId) ?? $currentUser;
        $opConfig           = $this->service->getOperatorConfig($plantCode, $selectedOperatorId);

        $filters = [
            'plant_code'  => $plantCode,
            'operator_id' => $selectedOperatorId,
            'date'        => $date,
            'shift'       => $shiftFilter,
        ];
        $checksheets = $this->service->getFilteredChecksheets($filters);

        $masterItems = $this->service->getMasterItems();
        $awalItems   = $masterItems->where('phase', 'awal')->values();
        $akhirItems  = $masterItems->where('phase', 'akhir')->values();

        // ponytail: chunk by 22 rows to fit A4 landscape cleanly alongside standard reference panel
        $chunks = !empty($checksheets->all()) ? array_chunk($checksheets->all(), 22, true) : [[]];
        $totalPages = count($chunks);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('dandory.print', compact(
            'plantCode', 'date', 'month', 'year', 'monthNames', 'shiftFilter',
            'selectedOperator', 'opConfig', 'awalItems', 'akhirItems', 'checksheets', 'chunks', 'totalPages'
        ));
    }

    /**
     * Simpan Sesi Awal Dandory Baru (Quick Modal)
     */
    public function storeAwal(Request $request)
    {
        $request->validate([
            'operator_id' => 'required|exists:users,id',
            'check_date'  => 'required|date',
            'shift'       => 'required|string',
            'table_no'    => 'required|string',
            'part_name'   => 'nullable|string',
            'item_id'     => 'nullable|string',
            'awal_checks' => 'nullable|array',
        ]);

        try {
            $record = $this->service->storeAwal($request->all(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Sesi Awal Dandory untuk ' . $record->part_name . ' berhasil disimpan.',
                'record'  => $record
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan Awal Dandory: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Selesaikan Sesi Akhir Dandory (Quick Modal)
     */
    public function storeAkhir(Request $request, $id)
    {
        $request->validate([
            'akhir_checks' => 'required|array',
            'notes'        => 'nullable|string|max:255',
        ]);

        try {
            $checksheet = DandoryChecksheet::findOrFail($id);
            if ($checksheet->leader_checked && Auth::user()->role === 'inspector') {
                return response()->json(['success' => false, 'message' => 'Sesi Dandory ini sudah diverifikasi Leader dan tidak dapat diubah.'], 403);
            }

            $record = $this->service->storeAkhir($checksheet, $request->akhir_checks, $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Akhir Dandory untuk ' . $record->part_name . ' berhasil diselesaikan.',
                'record'  => $record
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan Akhir Dandory: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle nilai sel matriks langsung via AJAX (✓ / ✕ / - / kosong)
     */
    public function toggleCell(Request $request, $id)
    {
        $request->validate([
            'phase'    => 'required|in:awal,akhir',
            'order_no' => 'required|integer',
            'status'   => 'nullable|in:OK,NG,NA',
        ]);

        $checksheet = DandoryChecksheet::findOrFail($id);
        if ($checksheet->leader_checked && Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Baris ini sudah diverifikasi Leader dan tidak dapat diubah.'], 403);
        }

        $record = $this->service->toggleCell($checksheet, $request->phase, (int)$request->order_no, $request->status);

        return response()->json([
            'success'      => true,
            'message'      => 'Nilai sel berhasil diperbarui.',
            'awal_checks'  => $record->awal_checks,
            'akhir_checks' => $record->akhir_checks,
        ]);
    }

    /**
     * Toggle Verifikasi Diperiksa Leader (Per Baris)
     */
    public function toggleVerify(Request $request, $id)
    {
        $allowedRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Hanya Leader (Kashift/Karu) atau SPV ke atas yang berhak melakukan verifikasi.'], 403);
        }

        $checksheet = DandoryChecksheet::findOrFail($id);

        // Jika ingin melakukan APPROVAL (bukan membatalkan approval)
        if (!$checksheet->leader_checked) {
            $masterItems = $this->service->getMasterItems();
            $minAwal = $masterItems->where('phase', 'awal')->count() ?: 9;
            $minAkhir = $masterItems->where('phase', 'akhir')->count() ?: 10;

            if (!$checksheet->isComplete($minAwal, $minAkhir)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum bisa melakukan approval! Pemeriksaan Dandory Awal dan Akhir harus diisi lengkap oleh Operator/Inspector terlebih dahulu.'
                ], 422);
            }
        }

        $record = $this->service->toggleLeaderVerification($checksheet, Auth::id(), Auth::user()->name);

        return response()->json([
            'success'        => true,
            'message'        => $record->leader_checked ? 'Baris berhasil diverifikasi Leader.' : 'Verifikasi Leader dibatalkan.',
            'leader_checked' => $record->leader_checked,
            'leader_name'    => $record->leader_name,
            'leader_time'    => $record->leader_checked_at ? $record->leader_checked_at->format('d/m/y H:i') : null,
            'is_rejected'    => false,
        ]);
    }

    /**
     * Reject Pemeriksaan Dandory oleh Leader
     */
    public function reject(Request $request, $id)
    {
        $allowedRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Hanya Leader (Kashift/Karu) atau SPV ke atas yang berhak melakukan reject.'], 403);
        }

        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        $checksheet = DandoryChecksheet::findOrFail($id);

        $masterItems = $this->service->getMasterItems();
        $minAwal = $masterItems->where('phase', 'awal')->count() ?: 9;
        $minAkhir = $masterItems->where('phase', 'akhir')->count() ?: 10;

        if (!$checksheet->isComplete($minAwal, $minAkhir)) {
            return response()->json([
                'success' => false,
                'message' => 'Belum bisa melakukan reject! Pemeriksaan Dandory Awal dan Akhir harus diisi lengkap oleh Operator/Inspector terlebih dahulu.'
            ], 422);
        }

        $record = $this->service->rejectChecksheet($checksheet, Auth::id(), Auth::user()->name, $request->input('remarks'));

        return response()->json([
            'success'           => true,
            'message'           => 'Pemeriksaan Dandory berhasil di-reject.',
            'is_rejected'       => true,
            'rejection_remarks' => $record->rejection_remarks,
            'rejector_name'     => $record->rejector_name,
            'rejected_at'       => $record->rejected_at ? $record->rejected_at->format('d/m/y H:i') : null,
        ]);
    }

    /**
     * Verifikasi Massal (Bulk Verify)
     */
    public function bulkVerify(Request $request)
    {
        $allowedRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Hanya Leader (Kashift/Karu) atau SPV ke atas yang berhak melakukan verifikasi massal.'], 403);
        }

        $ids = (array) ($request->get('ids') ?: []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada baris yang dipilih untuk diverifikasi.'], 422);
        }

        $masterItems = $this->service->getMasterItems();
        $minAwal = $masterItems->where('phase', 'awal')->count() ?: 9;
        $minAkhir = $masterItems->where('phase', 'akhir')->count() ?: 10;

        $selectedRows = DandoryChecksheet::whereIn('id', $ids)
            ->where('leader_checked', false)
            ->get();

        $validIds = [];
        $skippedCount = 0;
        foreach ($selectedRows as $cs) {
            if ($cs->isComplete($minAwal, $minAkhir)) {
                $validIds[] = $cs->id;
            } else {
                $skippedCount++;
            }
        }

        if (empty($validIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Semua data yang dipilih belum melengkapi pemeriksaan Dandory Awal dan Akhir, sehingga belum dapat diverifikasi Leader.'
            ], 422);
        }

        $count = $this->service->bulkVerify($validIds, Auth::id(), Auth::user()->name);

        $msg = "{$count} data checksheet Dandory berhasil diverifikasi Leader.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} data belum lengkap dilewati).";
        }

        return response()->json([
            'success'       => true,
            'message'       => $msg,
            'count'         => $count,
            'skipped_count' => $skippedCount,
        ]);
    }

    /**
     * Hapus Data Transaksi Dandory
     */
    public function destroy($id)
    {
        $checksheet = DandoryChecksheet::findOrFail($id);

        if ($checksheet->leader_checked && Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Data yang sudah diverifikasi Leader tidak dapat dihapus oleh Inspector.'], 403);
        }

        $checksheet->delete();

        return response()->json(['success' => true, 'message' => 'Data sesi Dandory berhasil dihapus.']);
    }

    /**
     * Hapus Massal Data Transaksi Dandory (Admin Only)
     */
    public function bulkDestroy(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang berhak melakukan penghapusan data massal.'], 403);
        }

        $ids = (array) ($request->get('ids') ?: []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada baris yang dipilih untuk dihapus.'], 422);
        }

        $count = DandoryChecksheet::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} data sesi Dandory berhasil dihapus.",
            'count'   => $count
        ]);
    }

    /**
     * Ambil smart preset (histori item '-' / NA pada part yang sama sebelumnya)
     */
    public function getPartPreset(Request $request)
    {
        $operatorId = (int) $request->get('operator_id');
        $itemId     = $request->get('item_id');

        $preset = $this->service->getLastSessionPartPreset($operatorId, $itemId);

        return response()->json(['success' => true, 'preset' => $preset]);
    }

    /**
     * Simpan Pengaturan Bagian Operator & Kategori Part (Non-Inspector)
     */
    public function saveOperatorConfig(Request $request)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Inspector tidak diizinkan mengubah pengaturan posisi operator.'], 403);
        }

        $request->validate([
            'plant_code'         => 'required|string',
            'operator_id'        => 'required|exists:users,id',
            'bagian'             => 'required|string',
            'default_table'      => 'nullable|string',
            'allowed_categories' => 'nullable|array',
        ]);

        $config = $this->service->saveOperatorConfig(
            $request->plant_code,
            (int) $request->operator_id,
            $request->all(),
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan posisi operator dan kategori part berhasil disimpan.',
            'config'  => $config
        ]);
    }

    /**
     * Simpan Pengaturan Daftar Meja per Plant (Non-Inspector)
     */
    public function saveTables(Request $request)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Inspector tidak diizinkan mengubah daftar meja.'], 403);
        }

        $request->validate([
            'plant_code' => 'required|string',
            'tables'     => 'required|array',
            'tables.*'   => 'required|string',
        ]);

        $this->service->savePlantTables($request->plant_code, $request->tables);

        return response()->json([
            'success' => true,
            'message' => 'Daftar meja untuk Plant ' . ucfirst($request->plant_code) . ' berhasil diperbarui.',
            'tables'  => $this->service->getPlantTables($request->plant_code)
        ]);
    }

    /**
     * Tambah Master Item Parameter Standar (Khusus Admin)
     */
    public function storeMasterItem(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang berhak mengelola master parameter Dandory.'], 403);
        }

        $request->validate([
            'phase'      => 'required|in:awal,akhir',
            'item_check' => 'required|string',
            'standard'   => 'required|string',
            'order_no'   => 'nullable|integer',
        ]);

        $maxOrder = DandoryItem::where('phase', $request->phase)->max('order_no') ?? 0;

        $item = DandoryItem::create([
            'phase'      => $request->phase,
            'item_check' => $request->item_check,
            'standard'   => $request->standard,
            'order_no'   => $request->order_no ?: ($maxOrder + 1),
            'is_active'  => true,
        ]);

        Cache::forget('dandory_master_items_active');

        return response()->json(['success' => true, 'message' => 'Master item parameter Dandory berhasil ditambahkan.', 'item' => $item]);
    }

    /**
     * Update Master Item Parameter Standar (Khusus Admin)
     */
    public function updateMasterItem(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang berhak mengelola master parameter Dandory.'], 403);
        }

        $request->validate([
            'item_check' => 'required|string',
            'standard'   => 'required|string',
            'order_no'   => 'nullable|integer',
        ]);

        $item = DandoryItem::findOrFail($id);
        $item->update([
            'item_check' => $request->item_check,
            'standard'   => $request->standard,
            'order_no'   => $request->order_no ?? $item->order_no,
        ]);

        Cache::forget('dandory_master_items_active');

        return response()->json(['success' => true, 'message' => 'Master item parameter Dandory berhasil diperbarui.', 'item' => $item]);
    }

    /**
     * Hapus Master Item Parameter Standar (Khusus Admin)
     */
    public function destroyMasterItem($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang berhak mengelola master parameter Dandory.'], 403);
        }

        $item = DandoryItem::findOrFail($id);
        $item->delete();

        Cache::forget('dandory_master_items_active');

        return response()->json(['success' => true, 'message' => 'Master item parameter Dandory berhasil dihapus.']);
    }
}

