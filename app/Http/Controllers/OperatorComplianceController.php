<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperatorComplianceItem;
use App\Models\OperatorComplianceChecksheet;
use App\Models\OperatorComplianceEntry;
use App\Models\OperatorComplianceProblem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OperatorComplianceController extends Controller
{
    /**
     * Display the main operator compliance checksheet.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $month       = (int) $request->get('month', date('n'));
        $year        = (int) $request->get('year', date('Y'));

        $plant = \App\Models\Plant::whereRaw('LOWER(code) = ?', [$plantCode])->first();

        // Retrieve inspector users strictly having role 'inspector' for selected plant
        $inspectorsQuery = User::where('role', 'inspector');
        if ($plant) {
            $inspectorsQuery->where('plant_id', $plant->id);
        }
        $inspectors = $inspectorsQuery->orderBy('name')->get();

        // Fallback: If no inspectors found for that specific plant_id, load all inspectors
        if ($inspectors->isEmpty()) {
            $inspectors = User::where('role', 'inspector')->orderBy('name')->get();
        }

        // Selected Operator ID - Default to the operator checksheet that was last updated
        $selectedOperatorId = (int) $request->get('operator_id');
        if (!$selectedOperatorId) {
            // Find operator checksheet last updated for this month & year and plant
            $latestChecksheet = OperatorComplianceChecksheet::where('plant_code', $plantCode)
                ->where('month', $month)
                ->where('year', $year)
                ->latest('updated_at')
                ->first();

            if (!$latestChecksheet) {
                // Fallback: find operator checksheet last updated overall for this plant
                $latestChecksheet = OperatorComplianceChecksheet::where('plant_code', $plantCode)
                    ->latest('updated_at')
                    ->first();
            }

            if (!$latestChecksheet) {
                // Fallback: find any operator checksheet last updated overall
                $latestChecksheet = OperatorComplianceChecksheet::latest('updated_at')->first();
            }

            if ($latestChecksheet) {
                $selectedOperatorId = $latestChecksheet->user_id;
            } else {
                // Fallback: current user if inspector, otherwise first inspector in list
                if (in_array($currentUser->role, ['inspector'])) {
                    $selectedOperatorId = $currentUser->id;
                } else {
                    $selectedOperatorId = $inspectors->first()?->id ?? $currentUser->id;
                }
            }
        }

        $selectedOperator = User::find($selectedOperatorId) ?? $currentUser;

        // Ensure selected operator is present in inspectors list for dropdown display
        if ($selectedOperator && !$inspectors->contains('id', $selectedOperator->id)) {
            $inspectors->push($selectedOperator);
        }

        // Active audit items
        $items = OperatorComplianceItem::where('is_active', true)
            ->orderBy('order_no')
            ->get();

        // Group items by Prinsip Dasar
        $groupedItems = $items->groupBy('prinsip_dasar');

        // Number of days in month
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // Find or create checksheet header
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

        // Load existing matrix entries
        $entries = OperatorComplianceEntry::where('checksheet_id', $checksheet->id)->get();
        $entriesMatrix = [];
        foreach ($entries as $entry) {
            $entriesMatrix[$entry->item_id][$entry->day] = $entry->status;
        }

        // Calculate daily and total scores
        $totalItemsCount = $items->count();
        $dailyScores = []; // day => ['ok' => count, 'ng' => count, 'na' => count, 'pct' => score]
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

            $filled = $okCount + $ngCount; // NA status is excluded from calculation
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

        // Load Problem Items
        $problems = OperatorComplianceProblem::where('checksheet_id', $checksheet->id)
            ->orderBy('problem_date', 'desc')
            ->get();

        return view('operator_compliance.index', compact(
            'plantCode',
            'month',
            'year',
            'daysInMonth',
            'inspectors',
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
            'problems'
        ));
    }

    /**
     * Render dedicated print layout for the operator compliance checksheet.
     * Accepts same query parameters as index().
     */
    public function print(Request $request)
    {
        $currentUser = Auth::user();
        $plantCode   = strtolower($request->get('plant', $currentUser->plant ? $currentUser->plant->code : 'karawang'));
        $month       = (int) $request->get('month', date('n'));
        $year        = (int) $request->get('year', date('Y'));

        $plant = \App\Models\Plant::whereRaw('LOWER(code) = ?', [$plantCode])->first();

        $inspectorsQuery = User::where('role', 'inspector');
        if ($plant) {
            $inspectorsQuery->where('plant_id', $plant->id);
        }
        $inspectors = $inspectorsQuery->orderBy('name')->get();

        if ($inspectors->isEmpty()) {
            $inspectors = User::where('role', 'inspector')->orderBy('name')->get();
        }

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
     * AJAX Endpoint to toggle/update a single matrix entry checkbox.
     */
    public function toggleEntry(Request $request)
    {
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

        $entry = OperatorComplianceEntry::where('checksheet_id', $checksheetId)
            ->where('item_id', $itemId)
            ->where('day', $day)
            ->first();

        $previousStatus = $entry ? $entry->status : null;

        if ($status === null || $status === '') {
            if ($entry) {
                $entry->delete();
            }
        } else {
            if ($entry) {
                $entry->status     = $status;
                $entry->updated_by = Auth::id();
                $entry->save();
            } else {
                $entry = OperatorComplianceEntry::create([
                    'checksheet_id' => $checksheetId,
                    'item_id'       => $itemId,
                    'day'           => $day,
                    'status'        => $status,
                    'updated_by'    => Auth::id(),
                ]);
            }
        }

        // Check if status changed from NG to non-NG, and if there's a linked problem to prompt for deletion
        $linkedProblemData = null;
        if ($previousStatus === 'NG' && $status !== 'NG') {
            $hasOtherNgForItem = OperatorComplianceEntry::where('checksheet_id', $checksheetId)
                ->where('item_id', $itemId)
                ->where('status', 'NG')
                ->exists();

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

        // Touch checksheet to update its updated_at timestamp
        $checksheet = OperatorComplianceChecksheet::find($checksheetId);
        if ($checksheet) {
            $checksheet->touch();
        }

        // Recalculate daily & monthly stats
        $allEntries = OperatorComplianceEntry::where('checksheet_id', $checksheetId)->get();

        $dayOk = $allEntries->where('day', $day)->where('status', 'OK')->count();
        $dayNg = $allEntries->where('day', $day)->where('status', 'NG')->count();
        $dayNa = $allEntries->where('day', $day)->where('status', 'NA')->count();
        $dayFilled = $dayOk + $dayNg; // NA excluded from calculation!
        $dayPct = $dayFilled > 0 ? round(($dayOk / $dayFilled) * 100, 1) : null;

        $totalOk = $allEntries->where('status', 'OK')->count();
        $totalNg = $allEntries->where('status', 'NG')->count();
        $totalNa = $allEntries->where('status', 'NA')->count();
        $totalFilled = $totalOk + $totalNg; // NA excluded from calculation!
        $monthlyPct = $totalFilled > 0 ? round(($totalOk / $totalFilled) * 100, 1) : 0;

        // Recalculate row score average for this item
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
     * Toggle verifikator sign/checkbox (Leader per-hari, SPV per-minggu, Manager 1x per-bulan).
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
     * Store or update a problem item.
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
     * Delete a problem log entry.
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
     * Store master item (Admin only).
     */
    public function storeMasterItem(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
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
     * Update master item (Admin only).
     */
    public function updateMasterItem(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
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
     * Delete/deactivate master item (Admin only).
     */
    public function destroyMasterItem($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $item = OperatorComplianceItem::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Master Item Audit berhasil dihapus.',
        ]);
    }
}
