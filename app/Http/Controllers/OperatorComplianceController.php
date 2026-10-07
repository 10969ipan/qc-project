<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperatorComplianceItem;
use App\Models\OperatorComplianceChecksheet;
use App\Models\OperatorComplianceEntry;
use App\Models\OperatorComplianceProblem;
use App\Models\OperatorComplianceSchedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OperatorComplianceController extends Controller
{
    /**
     * Tampilkan halaman utama checksheet kepatuhan operator quality
     */
    public function index(Request $request)
    {
        // Ambil data user yang lagi login
        $currentUser = Auth::user();

        // Tentukan plant, bulan, dan tahun (default dari user & tanggal sekarang)
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $month       = (int) $request->get('month', date('n'));
        $year        = (int) $request->get('year', date('Y'));

        // Cek hak akses menu untuk user saat ini (kecuali role admin)
        if ($currentUser->role !== 'admin') {
            $menu = \App\Models\AppMenu::where('name', 'Kepatuhan Operator')
                ->where('plant_code', $plantCode)
                ->first() 
                ?? \App\Models\AppMenu::where('name', 'Kepatuhan Operator')->first();

            if ($menu && !$currentUser->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki akses ke menu Kepatuhan Operator.');
            }
        }

        // Cari data plant sesuai kode plant
        $plant = \App\Models\Plant::whereRaw('LOWER(code) = ?', [$plantCode])->first();

        // Ambil daftar operator / inspector aktif khusus plant yang dipilih
        $plantOperatorsQuery = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true);
        if ($plant) {
            $plantOperatorsQuery->where('plant_id', $plant->id);
        }
        $plantOperators = $plantOperatorsQuery->orderBy('name')->get();

        // Untuk dropdown filter pilihan operator (fallback jika plant belum memiliki operator/inspector)
        if ($plantOperators->isEmpty() && (!$plant || $plantCode === 'total')) {
            $inspectors = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true)->orderBy('name')->get();
        } else {
            $inspectors = $plantOperators->concat([]);
        }

        // Tentukan ID operator yang dipilih (default otomatis pilih yang paling baru di-update)
        $selectedOperatorId = (int) $request->get('operator_id');
        if (!$selectedOperatorId) {
            // Cari checksheet yang paling baru diupdate di bulan, tahun & plant ini
            $latestChecksheet = OperatorComplianceChecksheet::where('plant_code', $plantCode)
                ->where('month', $month)
                ->where('year', $year)
                ->latest('updated_at')
                ->first();

            if (!$latestChecksheet) {
                // Cadangan: cari checksheet paling baru secara umum di plant ini
                $latestChecksheet = OperatorComplianceChecksheet::where('plant_code', $plantCode)
                    ->latest('updated_at')
                    ->first();
            }

            if (!$latestChecksheet) {
                // Cadangan: cari checksheet paling baru secara keseluruhan
                $latestChecksheet = OperatorComplianceChecksheet::latest('updated_at')->first();
            }

            if ($latestChecksheet) {
                $selectedOperatorId = $latestChecksheet->user_id;
            } else {
                // Cadangan terakhir: pakai ID user sekarang kalau inspector, atau inspector pertama dari list
                if (in_array($currentUser->role, ['inspector'])) {
                    $selectedOperatorId = $currentUser->id;
                } else {
                    $selectedOperatorId = $inspectors->first()?->id ?? $currentUser->id;
                }
            }
        }

        $selectedOperator = User::find($selectedOperatorId) ?? $currentUser;

        // Pastikan operator yang dipilih tetap ada di dropdown inspectors
        if ($selectedOperator && !$inspectors->contains('id', $selectedOperator->id)) {
            $inspectors->push($selectedOperator);
        }

        // Ambil semua item audit yang aktif
        $items = OperatorComplianceItem::where('is_active', true)
            ->orderBy('order_no')
            ->get();

        // Kelompokkan item audit berdasarkan Prinsip Dasar
        $groupedItems = $items->groupBy('prinsip_dasar');

        // Hitung total hari dalam bulan yang dipilih
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Cari atau buat header checksheet untuk operator, plant, bulan & tahun ini
        $checksheet = OperatorComplianceChecksheet::firstOrCreate(
            [
                'user_id'    => $selectedOperatorId,
                'plant_code' => $plantCode,
                'month'      => $month,
                'year'       => $year,
            ],
            [
                'bagian'     => $selectedOperator->bagian ?? 'Quality Control',
                'created_by' => $currentUser->id,
            ]
        );

        // Load entri matriks yang udah diisi (diatur berdasarkan item_id dan tanggal)
        $entries = OperatorComplianceEntry::where('checksheet_id', $checksheet->id)->get();
        $entriesMatrix = [];
        foreach ($entries as $entry) {
            $entriesMatrix[$entry->item_id][$entry->day] = $entry->status;
        }

        // Hitung skor harian dan total persentase bulanan
        $totalItemsCount = $items->count();
        $dailyScores = []; // tanggal => ['ok' => jumlah, 'ng' => jumlah, 'na' => jumlah, 'pct' => persentase]
        $totalOkMonth = 0;
        $totalNgMonth = 0;
        $totalFilledEntries = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $okCount = 0;
            $ngCount = 0;
            $naCount = 0;
            foreach ($items as $item) {
                $st = $entriesMatrix[$item->id][$d] ?? null;
                if ($st === 'OK') {
                    $okCount++;
                } elseif ($st === 'NG') {
                    $ngCount++;
                } elseif ($st === 'NA') {
                    $naCount++;
                }
            }

            $filled = $okCount + $ngCount; // Status NA tidak dihitung dalam pembagi skor
            $pct    = $filled > 0 ? round(($okCount / $filled) * 100, 1) : null;

            $dailyScores[$d] = [
                'ok'     => $okCount,
                'ng'     => $ngCount,
                'na'     => $naCount,
                'filled' => $filled,
                'pct'    => $pct,
            ];

            $totalOkMonth += $okCount;
            $totalNgMonth += $ngCount;
            $totalFilledEntries += $filled;
        }

        $monthlyPct = $totalFilledEntries > 0 ? round(($totalOkMonth / $totalFilledEntries) * 100, 1) : 0;

        // Ambil daftar item masalah (problem log) untuk checksheet ini
        $problems = OperatorComplianceProblem::where('checksheet_id', $checksheet->id)
            ->orderBy('problem_date', 'desc')
            ->get();

        // Ambil plan jadwal khusus untuk operator yang dipilih bulan ini (untuk disable cell di frontend)
        $planDays = OperatorComplianceSchedule::where('operator_id', $selectedOperatorId)
            ->whereYear('schedule_date', $year)
            ->whereMonth('schedule_date', $month)
            ->pluck('schedule_date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('j'); // Ambil tanggalnya aja (1-31)
            })
            ->toArray();

        // Ambil semua data master jadwal untuk modal "Kelola Jadwal Operator" (semua role kecuali inspector)
        $allSchedules = collect();
        if ($currentUser->role !== 'inspector') {
            $allSchedulesQuery = OperatorComplianceSchedule::with('operator');
            if ($plantCode !== 'total') {
                $allSchedulesQuery->where('plant', $plantCode);
            }
            $allSchedules = $allSchedulesQuery->orderBy('schedule_date', 'desc')->get();
        }

        return view('operator_compliance.index', compact(
            'plantCode',
            'month',
            'year',
            'daysInMonth',
            'inspectors',
            'plantOperators',
            'selectedOperatorId',
            'selectedOperator',
            'items',
            'groupedItems',
            'checksheet',
            'entriesMatrix',
            'dailyScores',
            'totalOkMonth',
            'totalNgMonth',
            'totalFilledEntries',
            'monthlyPct',
            'problems',
            'planDays',
            'allSchedules'
        ));
    }

    /**
     * Tampilkan halaman khusus cetak (print layout) checksheet kepatuhan operator
     */
    public function print(Request $request)
    {
        // Ambil data user yang lagi login
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $month       = (int) $request->get('month', date('n'));
        $year        = (int) $request->get('year', date('Y'));

        // Cek hak akses menu untuk user saat ini (kecuali role admin)
        if ($currentUser->role !== 'admin') {
            $menu = \App\Models\AppMenu::where('name', 'Kepatuhan Operator')
                ->where('plant_code', $plantCode)
                ->first() 
                ?? \App\Models\AppMenu::where('name', 'Kepatuhan Operator')->first();

            if ($menu && !$currentUser->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki akses ke menu Kepatuhan Operator.');
            }
        }

        $plant = \App\Models\Plant::whereRaw('LOWER(code) = ?', [$plantCode])->first();

        // Ambil daftar operator / inspector aktif
        $inspectorsQuery = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true);
        if ($plant) {
            $inspectorsQuery->where('plant_id', $plant->id);
        }
        $inspectors = $inspectorsQuery->orderBy('name')->get();

        if ($inspectors->isEmpty()) {
            $inspectors = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true)->orderBy('name')->get();
        }

        // Cari ID operator yang dipilih
        $selectedOperatorId = (int) $request->get('operator_id');
        if (!$selectedOperatorId) {
            $latestChecksheet = OperatorComplianceChecksheet::where('plant_code', $plantCode)
                ->where('month', $month)->where('year', $year)->latest('updated_at')->first()
                ?? OperatorComplianceChecksheet::where('plant_code', $plantCode)->latest('updated_at')->first()
                ?? OperatorComplianceChecksheet::latest('updated_at')->first();

            if ($latestChecksheet) {
                $selectedOperatorId = $latestChecksheet->user_id;
            } else {
                $selectedOperatorId = in_array($currentUser->role, ['inspector'])
                    ? $currentUser->id
                    : ($inspectors->first()?->id ?? $currentUser->id);
            }
        }

        $selectedOperator = User::find($selectedOperatorId) ?? $currentUser;

        if ($selectedOperator && !$inspectors->contains('id', $selectedOperator->id)) {
            $inspectors->push($selectedOperator);
        }

        // Ambil master item & hitung statistik sama kayak di index
        $items        = OperatorComplianceItem::where('is_active', true)->orderBy('order_no')->get();
        $groupedItems = $items->groupBy('prinsip_dasar');
        $daysInMonth  = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $checksheet = OperatorComplianceChecksheet::firstOrCreate(
            ['user_id' => $selectedOperatorId, 'plant_code' => $plantCode, 'month' => $month, 'year' => $year],
            ['bagian' => $selectedOperator->bagian ?? 'Quality Control', 'created_by' => $currentUser->id]
        );

        $entries       = OperatorComplianceEntry::where('checksheet_id', $checksheet->id)->get();
        $entriesMatrix = [];
        foreach ($entries as $entry) {
            $entriesMatrix[$entry->item_id][$entry->day] = $entry->status;
        }

        $dailyScores        = [];
        $totalOkMonth       = 0;
        $totalNgMonth       = 0;
        $totalFilledEntries = 0;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $okCount = $ngCount = $naCount = 0;
            foreach ($items as $item) {
                $st = $entriesMatrix[$item->id][$d] ?? null;
                if ($st === 'OK') $okCount++;
                elseif ($st === 'NG') $ngCount++;
                elseif ($st === 'NA') $naCount++;
            }
            $filled = $okCount + $ngCount;
            $pct    = $filled > 0 ? round(($okCount / $filled) * 100, 1) : null;
            $dailyScores[$d] = ['ok' => $okCount, 'ng' => $ngCount, 'na' => $naCount, 'filled' => $filled, 'pct' => $pct];
            $totalOkMonth       += $okCount;
            $totalNgMonth       += $ngCount;
            $totalFilledEntries += $filled;
        }

        $monthlyPct = $totalFilledEntries > 0 ? round(($totalOkMonth / $totalFilledEntries) * 100, 1) : 0;

        $problems = OperatorComplianceProblem::where('checksheet_id', $checksheet->id)
            ->orderBy('problem_date', 'desc')->get();

        return view('operator_compliance.print', compact(
            'plantCode', 'month', 'year', 'daysInMonth',
            'selectedOperator', 'items', 'groupedItems',
            'checksheet', 'entriesMatrix', 'dailyScores',
            'totalOkMonth', 'totalNgMonth', 'totalFilledEntries',
            'monthlyPct', 'problems'
        ));
    }

    /**
     * Endpoint AJAX buat update/toggle status ceklis harian (OK / NG / NA / kosong)
     */
    public function toggleEntry(Request $request)
    {
        // Validasi input data
        $request->validate([
            'checksheet_id' => 'required|exists:operator_compliance_checksheets,id',
            'item_id'       => 'required|exists:operator_compliance_items,id',
            'day'           => 'required|integer|min:1|max:31',
            'status'        => 'nullable|in:OK,NG,NA',
        ]);

        $checksheetId = $request->checksheet_id;
        $itemId       = $request->item_id;
        $day          = $request->day;
        $status       = $request->status;

        $checksheet = OperatorComplianceChecksheet::find($checksheetId);
        if (!$checksheet) {
            return response()->json(['success' => false, 'message' => 'Checksheet tidak ditemukan.'], 404);
        }

        // Cek validasi jadwal (Plan Input Data)
        $targetDate = Carbon::createFromDate($checksheet->year, $checksheet->month, $day)->format('Y-m-d');
        $hasPlan = OperatorComplianceSchedule::where('operator_id', $checksheet->user_id)
            ->where('schedule_date', $targetDate)
            ->exists();

        if (!$hasPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Belum masuk jadwal input. Plan data tidak ditemukan pada tanggal ini.'
            ], 403);
        }

        // Cari entri yang udah ada di database untuk cek status sebelumnya
        $entry = OperatorComplianceEntry::where('checksheet_id', $checksheetId)
            ->where('item_id', $itemId)
            ->where('day', $day)
            ->first();

        $previousStatus = $entry ? $entry->status : null;

        if ($status === null || $status === '') {
            // Kalau status dikosongkan, hapus dari database
            OperatorComplianceEntry::where('checksheet_id', $checksheetId)
                ->where('item_id', $itemId)
                ->where('day', $day)
                ->delete();
        } else {
            // Simpan atau update status baru secara atomic biar aman dari race-condition/double click
            OperatorComplianceEntry::updateOrCreate(
                [
                    'checksheet_id' => $checksheetId,
                    'item_id'       => $itemId,
                    'day'           => $day,
                ],
                [
                    'status'     => $status,
                    'updated_by' => Auth::id(),
                ]
            );
        }

        // Kalau status berubah dari NG jadi bukan NG, cek apakah masih ada temuan NG lain buat item ini
        $linkedProblemData = null;
        if ($previousStatus === 'NG' && $status !== 'NG') {
            $hasOtherNgForItem = OperatorComplianceEntry::where('checksheet_id', $checksheetId)
                ->where('item_id', $itemId)
                ->where('status', 'NG')
                ->exists();

            // Jika udah nggak ada NG sama sekali untuk item ini, siapkan data masalah untuk konfirmasi hapus di JS
            if (!$hasOtherNgForItem) {
                $linkedProblem = OperatorComplianceProblem::where('checksheet_id', $checksheetId)
                    ->where('item_id', $itemId)
                    ->first();

                if ($linkedProblem) {
                    $linkedProblemData = [
                        'id'          => $linkedProblem->id,
                        'description' => $linkedProblem->problem_description,
                        'date'        => $linkedProblem->problem_date ? $linkedProblem->problem_date->format('d/m/Y') : ''
                    ];
                }
            }
        }

        // Update timestamp updated_at pada checksheet
        $checksheet = OperatorComplianceChecksheet::find($checksheetId);
        if ($checksheet) {
            $checksheet->touch();
        }

        // Hitung ulang statistik harian & bulanan secara realtime
        $allEntries = OperatorComplianceEntry::where('checksheet_id', $checksheetId)->get();

        $dayOk = $allEntries->where('day', $day)->where('status', 'OK')->count();
        $dayNg = $allEntries->where('day', $day)->where('status', 'NG')->count();
        $dayNa = $allEntries->where('day', $day)->where('status', 'NA')->count();
        $dayFilled = $dayOk + $dayNg; // NA tidak dihitung dalam pembagi skor!
        $dayPct = $dayFilled > 0 ? round(($dayOk / $dayFilled) * 100, 1) : null;

        $totalOk = $allEntries->where('status', 'OK')->count();
        $totalNg = $allEntries->where('status', 'NG')->count();
        $totalNa = $allEntries->where('status', 'NA')->count();
        $totalFilled = $totalOk + $totalNg; // NA tidak dihitung dalam pembagi skor!
        $monthlyPct = $totalFilled > 0 ? round(($totalOk / $totalFilled) * 100, 1) : 0;

        // Hitung persentase rata-rata khusus baris item ini
        $itemEntries = $allEntries->where('item_id', $itemId);
        $itemOk = $itemEntries->where('status', 'OK')->count();
        $itemNg = $itemEntries->where('status', 'NG')->count();
        $itemFilled = $itemOk + $itemNg;
        $itemPct = $itemFilled > 0 ? round(($itemOk / $itemFilled) * 100) : '-';

        return response()->json([
            'success' => true,
            'status'  => $status,
            'linked_problem' => $linkedProblemData,
            'day_stats' => [
                'ok'     => $dayOk,
                'ng'     => $dayNg,
                'na'     => $dayNa,
                'pct'    => $dayPct,
            ],
            'monthly_stats' => [
                'total_ok'     => $totalOk,
                'total_ng'     => $totalNg,
                'total_na'     => $totalNa,
                'total_filled' => $totalFilled,
                'monthly_pct'  => $monthlyPct,
            ],
            'item_pct' => $itemPct,
        ]);
    }

    /**
     * Endpoint AJAX buat verifikasi (Leader harian, SPV mingguan, Manager bulanan)
     */
    public function toggleVerification(Request $request)
    {
        $request->validate([
            'checksheet_id' => 'required|exists:operator_compliance_checksheets,id',
            'type'          => 'required|in:leader,spv,mgr',
            'checked'       => 'required|boolean',
            'day'           => 'nullable|integer|min:1|max:31',
            'week'          => 'nullable|integer|min:1|max:5',
        ]);

        $cs = OperatorComplianceChecksheet::findOrFail($request->checksheet_id);
        $user = Auth::user();
        $checked = (bool) $request->checked;

        if ($request->type === 'leader') {
            $allowed = ['admin', 'kashift', 'kashift_qc', 'karu_qc', 'kashift_plating', 'karu_prod'];
            if (!in_array($user->role, $allowed)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya role Kashift / Karu yang dapat melakukan approval harian.'], 403);
            }
            // Verifikasi harian Leader / Kashift
            $day = (int) ($request->day ?? 1);
            $leaderChecks = $cs->leader_checks ?? [];
            if ($checked) {
                $leaderChecks[$day] = [
                    'checked'   => true,
                    'user_id'   => $user->id,
                    'user_name' => $user->name,
                    'time'      => now()->format('d/m/Y H:i'),
                ];
            } else {
                unset($leaderChecks[$day]);
            }
            $cs->leader_checks = $leaderChecks;

        } elseif ($request->type === 'spv') {
            $allowed = ['admin', 'supervisor', 'supervisor_qc', 'supervisor_plating'];
            if (!in_array($user->role, $allowed)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya role Supervisor yang dapat melakukan approval mingguan.'], 403);
            }
            // Verifikasi mingguan SPV / Karu
            $week = (int) ($request->week ?? 1);
            $spvChecks = $cs->spv_checks ?? [];
            if ($checked) {
                $spvChecks[$week] = [
                    'checked'   => true,
                    'user_id'   => $user->id,
                    'user_name' => $user->name,
                    'time'      => now()->format('d/m/Y H:i'),
                ];
            } else {
                unset($spvChecks[$week]);
            }
            $cs->spv_checks = $spvChecks;

        } elseif ($request->type === 'mgr') {
            $allowed = ['admin', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'];
            if (!in_array($user->role, $allowed)) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya role Asst Manager / Manager yang dapat melakukan approval bulanan.'], 403);
            }
            // Verifikasi bulanan Asst Mgr / Manager
            $cs->mgr_checked    = $checked;
            $cs->mgr_checked_at = $checked ? now() : null;
            $cs->mgr_id         = $checked ? $user->id : null;
        }

        $cs->save();

        return response()->json([
            'success'   => true,
            'type'      => $request->type,
            'day'       => $request->day,
            'week'      => $request->week,
            'checked'   => $checked,
            'user_name' => $user->name,
            'date'      => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Simpan atau update data item masalah abnormal (problem log)
     */
    public function storeProblem(Request $request)
    {
        $request->validate([
            'checksheet_id'       => 'required|exists:operator_compliance_checksheets,id',
            'problem_date'        => 'required|date',
            'problem_description' => 'required|string',
            'corrective_action'   => 'nullable|string',
            'pic_name'            => 'nullable|string',
            'target_date'         => 'nullable|date',
            'status'              => 'required|in:Open,In Progress,Closed',
        ]);

        $problemId = $request->problem_id;
        $data = [
            'checksheet_id'       => $request->checksheet_id,
            'item_id'             => $request->item_id ?: null,
            'problem_date'        => $request->problem_date,
            'problem_description' => $request->problem_description,
            'corrective_action'   => $request->corrective_action,
            'pic_name'            => $request->pic_name,
            'target_date'         => $request->target_date,
            'status'              => $request->status,
            'created_by'          => Auth::id(),
        ];

        if ($problemId) {
            $problem = OperatorComplianceProblem::findOrFail($problemId);
            $problem->update($data);
        } else {
            $problem = OperatorComplianceProblem::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data item masalah berhasil disimpan.',
            'problem' => $problem,
        ]);
    }

    /**
     * Hapus catatan item masalah dari database
     */
    public function destroyProblem($id)
    {
        $problem = OperatorComplianceProblem::findOrFail($id);
        $problem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data item masalah berhasil dihapus.',
        ]);
    }

    /**
     * Tambah item audit baru ke master (Role SPV ke Atas)
     */
    public function storeMasterItem(Request $request)
    {
        $allowedRoles = ['admin', 'manager', 'manager_plating', 'asst_manager', 'asst_manager_plating', 'supervisor', 'supervisor_plating'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'prinsip_dasar' => 'required|string',
            'item_check'    => 'required|string',
            'standard'      => 'required|string',
            'order_no'      => 'nullable|integer',
        ]);

        $maxOrder = OperatorComplianceItem::max('order_no') ?? 0;

        $item = OperatorComplianceItem::create([
            'prinsip_dasar' => $request->prinsip_dasar,
            'item_check'    => $request->item_check,
            'standard'      => $request->standard,
            'order_no'      => $request->order_no ?: ($maxOrder + 1),
            'is_active'     => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Master Item Audit berhasil ditambahkan.',
            'item'    => $item,
        ]);
    }

    /**
     * Update item audit master (Role SPV ke Atas)
     */
    public function updateMasterItem(Request $request, $id)
    {
        $allowedRoles = ['admin', 'manager', 'manager_plating', 'asst_manager', 'asst_manager_plating', 'supervisor', 'supervisor_plating'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'prinsip_dasar' => 'required|string',
            'item_check'    => 'required|string',
            'standard'      => 'required|string',
            'order_no'      => 'nullable|integer',
        ]);

        $item = OperatorComplianceItem::findOrFail($id);
        $item->update([
            'prinsip_dasar' => $request->prinsip_dasar,
            'item_check'    => $request->item_check,
            'standard'      => $request->standard,
            'order_no'      => $request->order_no ?? $item->order_no,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Master Item Audit berhasil diperbarui.',
            'item'    => $item,
        ]);
    }

    /**
     * Hapus item audit dari master (Role SPV ke Atas)
     */
    public function destroyMasterItem($id)
    {
        $allowedRoles = ['admin', 'manager', 'manager_plating', 'asst_manager', 'asst_manager_plating', 'supervisor', 'supervisor_plating'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $item = OperatorComplianceItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Master Item Audit berhasil dihapus.',
        ]);
    }

    /**
     * Halaman Utama Schedule Kepatuhan Operator (Bulanan)
     */
    public function schedule(Request $request)
    {
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $month       = (int) $request->get('month', date('n'));
        $year        = (int) $request->get('year', date('Y'));
        $shiftFilter        = $request->get('shift');
        $search             = $request->get('search');
        $selectedOperatorId = (int) $request->get('operator_id');

        // Default: jika filter shift tidak secara eksplisit ditentukan di query string,
        // tampilkan per shift dari jadwal yang terakhir di-update datanya
        if (!$request->has('shift')) {
            $latestSchedule = OperatorComplianceSchedule::where('plant', $plantCode)
                ->whereYear('schedule_date', $year)
                ->whereMonth('schedule_date', $month)
                ->whereNotNull('shift')
                ->where('shift', '!=', '')
                ->latest('updated_at')
                ->first();

            if (!$latestSchedule) {
                $latestSchedule = OperatorComplianceSchedule::where('plant', $plantCode)
                    ->whereNotNull('shift')
                    ->where('shift', '!=', '')
                    ->latest('updated_at')
                    ->first();
            }

            if ($latestSchedule && !empty($latestSchedule->shift)) {
                $shiftFilter = $latestSchedule->shift;
            }
        }

        // Cek hak akses
        if ($currentUser->role !== 'admin') {
            $menu = \App\Models\AppMenu::where('name', 'Kepatuhan Operator')
                ->where('plant_code', $plantCode)
                ->first() 
                ?? \App\Models\AppMenu::where('name', 'Kepatuhan Operator')->first();

            if ($menu && !$currentUser->hasPermission($menu->id, 'view')) {
                abort(403, 'Anda tidak memiliki akses ke menu ini.');
            }
        }

        // Cari data plant sesuai kode plant
        $plant = \App\Models\Plant::whereRaw('LOWER(code) = ?', [$plantCode])->first();

        // Ambil daftar operator / inspector aktif khusus plant yang dipilih
        $plantOperatorsQuery = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true);
        if ($plant) {
            $plantOperatorsQuery->where('plant_id', $plant->id);
        }
        $plantOperators = $plantOperatorsQuery->orderBy('name')->get();

        if ($plantOperators->isEmpty() && (!$plant || $plantCode === 'total')) {
            $inspectors = User::whereIn('role', ['operator', 'inspector'])->where('is_active', true)->orderBy('name')->get();
        } else {
            $inspectors = $plantOperators->concat([]);
        }

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $schedulesQuery = OperatorComplianceSchedule::where('plant', $plantCode)
            ->whereYear('schedule_date', $year)
            ->whereMonth('schedule_date', $month)
            ->with('operator');

        if ($selectedOperatorId) {
            $schedulesQuery->where('operator_id', $selectedOperatorId);
        }
            
        if ($shiftFilter) {
            $schedulesQuery->where('shift', $shiftFilter);
        }
        
        if ($search) {
            $schedulesQuery->whereHas('operator', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        $schedules = $schedulesQuery->get();
        
        // Group by operator_id 
        $operatorRows = [];
        foreach ($schedules as $sch) {
            $opId = $sch->operator_id;
            if (!isset($operatorRows[$opId])) {
                $operatorRows[$opId] = [
                    'operator' => $sch->operator,
                    'bagian'   => $sch->bagian ?: ($sch->operator?->bagian ?? 'Quality Control'),
                    'plans'    => [],
                    'actuals'  => []
                ];
            }
            $d = (int) Carbon::parse($sch->schedule_date)->format('j');
            $operatorRows[$opId]['plans'][$d] = true;
        }

        // Ambil data checksheet actual untuk bulan & tahun ini
        $checksheetsQuery = OperatorComplianceChecksheet::where('plant_code', $plantCode)
            ->where('year', $year)
            ->where('month', $month)
            ->with('operator');

        if ($selectedOperatorId) {
            $checksheetsQuery->where('user_id', $selectedOperatorId);
        }

        if ($search) {
            $checksheetsQuery->whereHas('operator', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $checksheets = $checksheetsQuery->get();
            
        foreach ($checksheets as $cs) {
            $opId = $cs->user_id;

            $entriesDays = OperatorComplianceEntry::where('checksheet_id', $cs->id)
                            ->select('day')
                            ->distinct()
                            ->pluck('day')
                            ->toArray();

            if (!empty($entriesDays)) {
                if (!isset($operatorRows[$opId])) {
                    if ($shiftFilter) {
                        continue;
                    }
                    $operatorRows[$opId] = [
                        'operator' => $cs->operator,
                        'bagian'   => $cs->bagian ?: 'Quality Control',
                        'plans'    => [],
                        'actuals'  => []
                    ];
                }
                foreach ($entriesDays as $d) {
                    $operatorRows[$opId]['actuals'][(int)$d] = true;
                }
            }
        }

        // Hitung total plan dan total actual per operator
        foreach ($operatorRows as $opId => &$row) {
            $row['total_plan']   = count($row['plans']);
            $row['total_actual'] = count($row['actuals']);
        }
        unset($row);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('operator_compliance.schedule', compact(
            'plantCode', 'month', 'year', 'daysInMonth', 'operatorRows', 'monthNames', 'shiftFilter', 'search', 'inspectors', 'selectedOperatorId'
        ));
    }

    /**
     * Tambah/Update Jadwal Operator (Semua role kecuali Inspector)
     */
    public function scheduleStore(Request $request)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Inspector tidak diizinkan mengelola jadwal.'], 403);
        }

        $request->validate([
            'operator_id'      => 'required|exists:users,id',
            'bagian'           => 'nullable|string',
            'shift'            => 'nullable|string',
            'schedule_date'    => 'nullable|date',
            'schedule_dates'   => 'nullable|array',
            'schedule_dates.*' => 'date',
        ]);

        $dates = (array) ($request->get('schedule_dates') ?: []);
        if ($request->schedule_date && !in_array($request->schedule_date, $dates)) {
            $dates[] = $request->schedule_date;
        }

        if (empty($dates)) {
            return response()->json(['success' => false, 'message' => 'Silakan tentukan minimal satu tanggal plan.'], 422);
        }

        $operator = User::with('plant')->find($request->operator_id);
        $plantCode = strtolower($request->get('plant', $operator?->plant?->code ?? (Auth::user()->plant ? Auth::user()->plant->code : 'karawang')));

        $saved = [];
        foreach ($dates as $date) {
            $formattedDate = Carbon::parse($date)->format('Y-m-d');
            $schedule = OperatorComplianceSchedule::updateOrCreate(
                [
                    'plant'         => $plantCode,
                    'operator_id'   => $request->operator_id,
                    'schedule_date' => $formattedDate,
                ],
                [
                    'bagian'        => $request->bagian,
                    'shift'         => $request->shift,
                ]
            );
            $saved[] = $schedule;
        }

        return response()->json([
            'success' => true,
            'message' => count($saved) > 1 
                ? count($saved) . ' Jadwal Operator berhasil disimpan.' 
                : 'Jadwal Operator berhasil disimpan.',
            'schedules'=> $saved
        ]);
    }

    /**
     * Update Jadwal Operator (Semua role kecuali Inspector)
     */
    public function scheduleUpdate(Request $request, $id)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Inspector tidak diizinkan mengelola jadwal.'], 403);
        }

        $request->validate([
            'operator_id'   => 'required|exists:users,id',
            'bagian'        => 'nullable|string',
            'shift'         => 'nullable|string',
            'schedule_date' => 'required|date',
        ]);

        $schedule = OperatorComplianceSchedule::findOrFail($id);

        $operator = User::with('plant')->find($request->operator_id);
        $plantCode = strtolower($request->get('plant', $operator?->plant?->code ?? (Auth::user()->plant ? Auth::user()->plant->code : 'karawang')));

        $formattedDate = Carbon::parse($request->schedule_date)->format('Y-m-d');

        // Cek duplikasi jadwal untuk operator dan tanggal yang sama pada plant ini (kecuali record ini sendiri)
        $duplicate = OperatorComplianceSchedule::where('plant', $plantCode)
            ->where('operator_id', $request->operator_id)
            ->where('schedule_date', $formattedDate)
            ->where('id', '!=', $id)
            ->first();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal untuk operator ini pada tanggal tersebut sudah ada.'
            ], 422);
        }

        $schedule->update([
            'plant'         => $plantCode,
            'operator_id'   => $request->operator_id,
            'bagian'        => $request->bagian,
            'shift'         => $request->shift,
            'schedule_date' => $formattedDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Operator berhasil diperbarui.',
            'schedule'=> $schedule
        ]);
    }

    /**
     * Hapus Jadwal Operator (Semua role kecuali Inspector)
     */
    public function scheduleDestroy($id)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Inspector tidak diizinkan mengelola jadwal.'], 403);
        }

        $schedule = OperatorComplianceSchedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal Operator berhasil dihapus.',
        ]);
    }

    /**
     * Sinkronisasi riwayat checksheet ke tabel jadwal Plan (P)
     * Hanya dapat dijalankan oleh role Admin
     */
    public function syncHistoricalSchedules(Request $request)
    {
        if (Auth::user()->role === 'inspector') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Role Inspector tidak diizinkan melakukan sinkronisasi.'], 403);
        }

        $checksheets = OperatorComplianceChecksheet::with(['operator', 'entries'])->get();

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($checksheets as $cs) {
            if (!$cs->user_id) {
                continue;
            }

            $days = $cs->entries->pluck('day')->unique()->filter()->values();
            if ($days->isEmpty()) {
                continue;
            }

            $plant = strtolower($cs->plant_code ?: 'karawang');
            $bagian = $cs->bagian ?: 'Quality Control';

            foreach ($days as $day) {
                $dateStr = sprintf('%04d-%02d-%02d', $cs->year, $cs->month, (int)$day);

                $exists = OperatorComplianceSchedule::where('plant', $plant)
                    ->where('operator_id', $cs->user_id)
                    ->whereDate('schedule_date', $dateStr)
                    ->first();

                if ($exists) {
                    $skippedCount++;
                } else {
                    OperatorComplianceSchedule::create([
                        'plant'         => $plant,
                        'operator_id'   => $cs->user_id,
                        'bagian'        => $bagian,
                        'shift'         => 'Non Shift',
                        'schedule_date' => $dateStr,
                    ]);
                    $createdCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sinkronisasi selesai! {$createdCount} data Plan (P) berhasil dibuat, {$skippedCount} jadwal yang sudah ada dilewati.",
            'created' => $createdCount,
            'skipped' => $skippedCount
        ]);
    }
}

