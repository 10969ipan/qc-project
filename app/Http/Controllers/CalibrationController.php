<?php

namespace App\Http\Controllers;

use App\Models\CalibrationTool;
use App\Models\CalibrationToolLog;
use App\Models\CalibrationVerification;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use SimpleSoftwareIO\QrCode\Facades\QrCode; // Temporarily disabled - install library first
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use setasign\Fpdi\Fpdi;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;

class CalibrationController extends Controller
{
    public function scheduleIndex(Request $request)
    {
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $year = date('Y');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = CalibrationTool::where('plant_id', $plant->id)
            ->where('status', '!=', 'BROKEN')
            ->with([
                'verifications' => function ($q) use ($year, $startDate, $endDate) {
                    $q->whereYear('tanggal_verifikasi', $year);
                    if ($startDate)
                        $q->whereDate('tanggal_verifikasi', '>=', $startDate);
                    if ($endDate)
                        $q->whereDate('tanggal_verifikasi', '<=', $endDate);
                },
                'schedules' => function ($q) use ($year, $startDate, $endDate) {
                    $q->whereYear('schedule_date', $year);
                    if ($startDate)
                        $q->whereDate('schedule_date', '>=', $startDate);
                    if ($endDate)
                        $q->whereDate('schedule_date', '<=', $endDate);
                },
                'latestVerification'
            ]);

        // Filter Tool ID (Click-to-filter dari Chart)
        if ($request->filled('tool_id')) {
            $query->where('id', $request->tool_id);
        }

        // Filter Tanggal Schedule Planning (New Logic)
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                // Check in multiple schedules table
                $q->whereHas('schedules', function ($sq) use ($startDate, $endDate) {
                    if ($startDate)
                        $sq->whereDate('schedule_date', '>=', $startDate);
                    if ($endDate)
                        $sq->whereDate('schedule_date', '<=', $endDate);
                });

                // OR check in legacy schedule_planning field
                $q->orWhere(function ($lq) use ($startDate, $endDate) {
                    if ($startDate)
                        $lq->whereDate('schedule_planning', '>=', $startDate);
                    if ($endDate)
                        $lq->whereDate('schedule_planning', '<=', $endDate);
                });
            });
        }

        // Filter Frekuensi Kalibrasi
        if ($request->filled('frequency')) {
            if ($request->frequency === '1_year') {
                $query->where('frekuensi_kalibrasi', 'LIKE', '%1 TAHUN%')
                    ->orWhere('frekuensi_kalibrasi', 'LIKE', '%1 YEAR%');
            } elseif ($request->frequency === 'more_than_1_year') {
                $query->where(function ($q) {
                    $q->where('frekuensi_kalibrasi', 'REGEXP', '[2-9] TAHUN|[2-9] YEAR|TAHUN|YEAR')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%1 TAHUN%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%1 YEAR%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%BULAN%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%MONTH%');
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bagian', 'LIKE', "%{$search}%")
                    ->orWhere('name_alat', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('jenis_kalibrasi')) {
            $query->where('jenis_kalibrasi', $request->jenis_kalibrasi);
        }

        $tools = $query->get();

        return view('calibration.schedule.index', compact('tools', 'plantCode'));
    }

    public function toolsIndex(Request $request)
    {
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $query = CalibrationTool::where('plant_id', $plant->id)
            ->with([
                'verifications' => function ($q) {
                    $q->whereYear('tanggal_verifikasi', date('Y'));
                },
                'schedules' => function ($q) {
                    $q->whereYear('schedule_date', date('Y'));
                }
            ]);

        // Filter Tool ID (Click-to-filter dari Chart)
        if ($request->filled('tool_id')) {
            $query->where('id', $request->tool_id);
        }

        // Filter Tanggal Schedule Planning
        if ($request->filled('start_date')) {
            $query->whereDate('schedule_planning', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('schedule_planning', '<=', $request->end_date);
        }

        // Filter Frekuensi Kalibrasi
        if ($request->filled('frequency')) {
            if ($request->frequency === '1_year') {
                $query->where('frekuensi_kalibrasi', 'LIKE', '%1 TAHUN%')
                    ->orWhere('frekuensi_kalibrasi', 'LIKE', '%1 YEAR%');
            } elseif ($request->frequency === 'more_than_1_year') {
                $query->where(function ($q) {
                    $q->where('frekuensi_kalibrasi', 'REGEXP', '[2-9] TAHUN|[2-9] YEAR|TAHUN|YEAR')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%1 TAHUN%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%1 YEAR%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%BULAN%')
                        ->where('frekuensi_kalibrasi', 'NOT LIKE', '%MONTH%');
                });
            }
        }

        // Filter Jenis Kalibrasi
        if ($request->filled('jenis_kalibrasi')) {
            $query->where('jenis_kalibrasi', $request->jenis_kalibrasi);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bagian', 'LIKE', "%{$search}%")
                    ->orWhere('name_alat', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhere('range', 'LIKE', "%{$search}%")
                    ->orWhere('resolusi', 'LIKE', "%{$search}%")
                    ->orWhere('lokasi_pakai', 'LIKE', "%{$search}%")
                    ->orWhere('frekuensi_kalibrasi', 'LIKE', "%{$search}%")
                    ->orWhere('riwayat_kalibrasi', 'LIKE', "%{$search}%")
                    ->orWhere('jenis_kalibrasi', 'LIKE', "%{$search}%")
                    ->orWhereHas('schedules', function ($q2) use ($search) {
                        $q2->where('pr_number', 'LIKE', "%{$search}%");
                    });

                // Special handling for date searching if needed, 
                // but usually simple LIKE is enough for parts of date strings
                $q->orWhere('tanggal_beli', 'LIKE', "%{$search}%");
            });
        }

        $tools = $query->get();

        // Filter by Verification Status (OK / Belum Verifikasi)
        if ($request->filled('verification_status')) {
            $statusFilter = $request->verification_status;
            $tools = $tools->filter(function ($tool) use ($statusFilter) {
                $scheduledStatuses = $tool->getScheduledStatuses(date('Y'));

                if (empty($scheduledStatuses))
                    return false;

                if ($statusFilter === 'ok') {
                    // Has at least one 'OK'
                    return collect($scheduledStatuses)->contains('is_ok', true);
                } elseif ($statusFilter === 'pending') {
                    // Has at least one 'Belum Verifikasi'
                    return collect($scheduledStatuses)->contains('is_ok', false);
                }

                return true;
            });
        }

        return view('calibration.tools.index', compact('tools', 'plantCode'));
    }



    public function toolsStore(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $this->validateFileUpload($request, 'certification');

        $request->validate([
            'plant' => 'required|string',
            'bagian' => 'required|string',
            'name_alat' => 'required|string',
            'merk' => 'nullable|string',
            'serial_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $plant = Plant::where('code', $request->plant)->first();
                    if ($plant) {
                        $exists = CalibrationTool::where('serial_number', $value)
                            ->where('plant_id', $plant->id)
                            ->exists();
                        if ($exists) {
                            $fail('Serial Number ini sudah terdaftar di plant ini.');
                        }
                    }
                }
            ],
            'range' => 'required|string',
            'resolusi' => 'required|string',
            'lokasi_pakai' => 'required|string',
            'tanggal_beli' => 'required|date',
            'frekuensi_kalibrasi' => 'required|string',
            'jenis_kalibrasi' => 'required|string',
            'schedule_planning' => 'required|array',
            'schedule_planning.*' => 'required|date',
            'certification' => 'nullable|sometimes|file|mimes:pdf|max:10240',
        ]);

        $plant = Plant::where('code', $request->plant)->first();

        $data = $request->except(['certification', 'plant', 'schedule_planning']);
        $data['plant_id'] = $plant->id;

        // Use the first schedule as the main schedule_planning for legacy purposes
        $data['schedule_planning'] = $request->schedule_planning[0];

        if ($request->hasFile('certification')) {
            if (!Storage::disk('public')->exists('calibration/tools')) {
                Storage::disk('public')->makeDirectory('calibration/tools');
            }

            $file = $request->file('certification');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('calibration/tools', $filename, 'public');
            $data['certification_path'] = $path;
        }

        // Removal of strtoupper logic for jenis_kalibrasi as per user request for case-sensitive data
        $tool = CalibrationTool::create($data);

        // Save multiple schedules
        foreach ($request->schedule_planning as $date) {
            $tool->schedules()->create(['schedule_date' => $date]);
        }

        return redirect()->route('calibration.tools.index', ['plant' => $request->plant])
            ->with('success', 'Master Data Alat berhasil disimpan.');
    }

    public function toolsEdit($id, Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $tool = CalibrationTool::findOrFail($id);
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        if ($request->ajax()) {
            $tool->load('schedules');

            // Format dates to prevent timezone shifts in JS serialization
            if ($tool->tanggal_beli) {
                $tool->tanggal_beli_formatted = $tool->tanggal_beli->format('Y-m-d');
            }

            $tool->schedules->each(function ($sch) {
                $sch->schedule_date_formatted = $sch->schedule_date->format('Y-m-d');
            });

            return response()->json([
                'tool' => $tool,
                'plantCode' => $plantCode
            ]);
        }
        return view('calibration.tools.edit', compact('tool', 'plantCode'));
    }

    public function toolsUpdate(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $this->validateFileUpload($request, 'certification');

        $request->validate([
            'plant' => 'required|string',
            'bagian' => 'required|string',
            'name_alat' => 'required|string',
            'merk' => 'nullable|string',
            'serial_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $plant = Plant::where('code', $request->plant)->first();
                    if ($plant) {
                        $exists = CalibrationTool::where('serial_number', $value)
                            ->where('plant_id', $plant->id)
                            ->where('id', '!=', $id)
                            ->exists();
                        if ($exists) {
                            $fail('Serial Number ini sudah digunakan oleh alat lain di plant ini.');
                        }
                    }
                }
            ],
            'range' => 'nullable|string',
            'resolusi' => 'nullable|string',
            'lokasi_pakai' => 'nullable|string',
            'tanggal_beli' => 'nullable|date',
            'frekuensi_kalibrasi' => 'required|string',
            'jenis_kalibrasi' => 'required|string',
            'schedule_planning' => 'nullable|array',
            'schedule_planning.*' => 'nullable|date',
            'certification' => 'nullable|sometimes|file|mimes:pdf|max:10240',
        ]);

        $tool = CalibrationTool::findOrFail($id);
        $data = $request->except(['certification', 'plant', 'schedule_planning']);

        // Use the first schedule as the main schedule_planning for legacy purposes
        $data['schedule_planning'] = !empty($request->schedule_planning) ? $request->schedule_planning[0] : null;

        if ($request->hasFile('certification')) {
            if (!Storage::disk('public')->exists('calibration/tools')) {
                Storage::disk('public')->makeDirectory('calibration/tools');
            }

            // Delete old file if exists
            if ($tool->certification_path) {
                Storage::disk('public')->delete($tool->certification_path);
            }

            $file = $request->file('certification');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('calibration/tools', $filename, 'public');
            $data['certification_path'] = $path;
        }

        // Removal of strtoupper logic for jenis_kalibrasi as per user request for case-sensitive data
        $tool->update($data);

        // Sync schedules: Match by ID to preserve PR numbers/dates
        $inputDates = $request->input('schedule_planning', []);
        $inputIds = $request->input('schedule_ids', []); // IDs of existing schedules to keep/update
        $inputPrNumbers = $request->input('schedule_pr_numbers', []); // PR numbers from edit form

        $existingIds = $tool->schedules()->pluck('id')->toArray();

        // 1. Delete schedules that are no longer in the request
        $toDelete = array_diff($existingIds, $inputIds);
        if (!empty($toDelete)) {
            $tool->schedules()->whereIn('id', $toDelete)->delete();
        }

        // 2. Update or Create schedules
        foreach ($inputDates as $index => $date) {
            $scheduleId = isset($inputIds[$index]) ? $inputIds[$index] : null;
            $prNumber = isset($inputPrNumbers[$index]) ? $inputPrNumbers[$index] : null;

            if ($scheduleId && in_array($scheduleId, $existingIds)) {
                // Update existing
                $updateData = ['schedule_date' => $date];

                // Only update PR if it's provided in the edit form
                // This allows maintaining existing PRs if they were already there
                $currentSch = $tool->schedules()->find($scheduleId);
                if ($prNumber !== null && $currentSch->pr_number !== $prNumber) {
                    $updateData['pr_number'] = $prNumber;
                    $updateData['pr_date'] = empty($prNumber) ? null : now();
                }

                $tool->schedules()->where('id', $scheduleId)->update($updateData);
            } else {
                // Create new
                $tool->schedules()->create([
                    'schedule_date' => $date,
                    'pr_number' => $prNumber,
                    'pr_date' => empty($prNumber) ? null : now()
                ]);
            }
        }

        return redirect()->route('calibration.tools.index', ['plant' => $request->plant])
            ->with('success', 'Master Data Alat berhasil diperbarui.');
    }

    public function toolsDestroy($id, Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $tool = CalibrationTool::findOrFail($id);

        // Delete associated certification file
        if ($tool->certification_path) {
            Storage::disk('public')->delete($tool->certification_path);
        }

        // Delete associated verifications
        foreach ($tool->verifications as $v) {
            if ($v->certification_path) {
                Storage::disk('public')->delete($v->certification_path);
            }
            $v->delete();
        }

        $tool->delete();

        return redirect()->route('calibration.tools.index', ['plant' => $request->get('plant', 'jakarta')])
            ->with('success', 'Master Data Alat dan seluruh riwayat verifikasinya berhasil dihapus.');
    }

    public function storeProblemLog(Request $request)
    {
        $request->validate([
            'calibration_tool_id' => 'required|exists:calibration_tools,id',
            'problem_type' => 'required|in:ERROR,RUSAK',
            'description' => 'required|string',
            'reported_date' => 'required|date',
            'action_taken' => 'required|string',
            'plant' => 'required|string',
            'evidence_report' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $tool = CalibrationTool::findOrFail($request->calibration_tool_id);
        $actionTaken = $request->action_taken;

        if ($request->problem_type === 'RUSAK') {
            $tool->update(['status' => 'BROKEN']);
            // Soft Delete schedules so they can be restored if judgment is OK
            $tool->schedules()->delete();
        }

        $evidencePath = null;
        if ($request->hasFile('evidence_report')) {
            $evidencePath = $request->file('evidence_report')->store('calibration/evidence', 'public');
        }

        $tool->logs()->create([
            'problem_type' => $request->problem_type,
            'action_taken' => $actionTaken,
            'description' => $request->description,
            'reported_date' => $request->reported_date,
            'evidence_report' => $evidencePath,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('calibration.tools.index', ['plant' => $request->plant])
            ->with('success', 'Laporan masalah berhasil disimpan.');
    }

    public function updateProblemJudgment(Request $request, $id)
    {
        $log = CalibrationToolLog::findOrFail($id);
        $tool = $log->tool;

        // Authorization check (moved from original position, but still relevant)
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'spv'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'judgment_status' => 'required|in:OK,NG',
            'judgment_remarks' => 'nullable|string',
            'plant' => 'required|string',
            'evidence_judgment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $updateData = [
            'judgment_status' => $request->judgment_status,
            'judgment_remarks' => $request->judgment_remarks,
            'judged_by' => auth()->id(),
            'judged_at' => now(),
        ];

        if ($request->hasFile('evidence_judgment')) {
            // Delete old file if exists
            if ($log->evidence_judgment) {
                Storage::disk('public')->delete($log->evidence_judgment);
            }
            $updateData['evidence_judgment'] = $request->file('evidence_judgment')->store('calibration/evidence', 'public');
        }

        $log->update($updateData);

        if ($request->judgment_status === 'OK') {
            // Restore tool if it was broken
            if ($tool->status === 'BROKEN') {
                $tool->update(['status' => 'ACTIVE']);
                // Restore soft-deleted schedules
                $tool->schedules()->restore();
            }
            $message = 'Judgment OK berhasil disimpan. Alat kembali ACTIVE dan jadwal direstore.';
        } else {
            // If NG, soft-delete the tool to remove from master data & schedules
            // Log will remain visible because we'll use withTrashed() in the report query
            $tool->schedules()->delete();
            $tool->delete();
            $message = 'Judgment NG berhasil disimpan. Alat telah dihapus dari master data.';
        }

        return redirect()->route('calibration.tools.problem-logs', ['plant' => $request->plant])
            ->with('success', $message);
    }

    public function updateProblemLog(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'spv'])) {
            abort(403, 'Unauthorized action.');
        }

        $log = CalibrationToolLog::findOrFail($id);
        $tool = $log->tool;

        $request->validate([
            'problem_type' => 'required|in:ERROR,RUSAK',
            'description' => 'required|string',
            'reported_date' => 'required|date',
            'action_taken' => 'required|string',
            'plant' => 'required|string',
            'evidence_report' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $oldType = $log->problem_type;
        $newType = $request->problem_type;

        if ($oldType !== $newType) {
            if ($newType === 'RUSAK') {
                $tool->update(['status' => 'BROKEN']);
                $tool->schedules()->delete();
            } else {
                $tool->update(['status' => 'ACTIVE']);
                $tool->schedules()->restore();
            }
        }

        $updateData = [
            'problem_type' => $request->problem_type,
            'description' => $request->description,
            'reported_date' => $request->reported_date,
            'action_taken' => $request->action_taken,
        ];

        if ($request->hasFile('evidence_report')) {
            // Delete old file if exists
            if ($log->evidence_report) {
                Storage::disk('public')->delete($log->evidence_report);
            }
            $updateData['evidence_report'] = $request->file('evidence_report')->store('calibration/evidence', 'public');
        }

        $log->update($updateData);

        return redirect()->back()->with('success', 'Laporan masalah berhasil diperbarui.');
    }

    public function destroyProblemLog($id, Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'asst_manager', 'spv'])) {
            abort(403, 'Unauthorized action.');
        }

        $log = CalibrationToolLog::findOrFail($id);

        if ($log->problem_type === 'RUSAK' && $log->tool->status === 'BROKEN') {
            $log->tool->update(['status' => 'ACTIVE']);
            $log->tool->schedules()->restore();
        }

        $log->delete();

        return redirect()->back()->with('success', 'Laporan masalah berhasil dihapus.');
    }

    public function problemLogs(Request $request)
    {
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $logs = CalibrationToolLog::with([
            'tool' => function ($q) {
                $q->withTrashed();
            },
            'tool.plant',
            'user'
        ])
            ->whereHas('tool', function ($q) use ($plant) {
                $q->withTrashed()->where('plant_id', $plant->id);
            })
            ->latest('reported_date')
            ->get();

        return view('calibration.tools.problem_logs', compact('logs', 'plantCode'));
    }

    public function verificationsIndex(Request $request)
    {
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $query = CalibrationVerification::where('plant_id', $plant->id)
            ->with('tool');

        // Filter Tanggal Verifikasi
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_verifikasi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_verifikasi', '<=', $request->end_date);
        }

        // Filter Tool ID (Click-to-filter dari Chart Actual)
        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }

        $verifications = $query->latest()->get();

        $tools = CalibrationTool::where('plant_id', $plant->id)->with('schedules')->orderBy('name_alat')->get();

        return view('calibration.verifications.index', compact('verifications', 'plantCode', 'tools'));
    }

    public function verificationsPdf(Request $request)
    {
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $query = CalibrationVerification::where('plant_id', $plant->id)
            ->with('tool');

        // Filter Tanggal Verifikasi
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_verifikasi', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_verifikasi', '<=', $request->end_date);
        }

        // Filter Tool ID
        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }

        $verifications = $query->latest()->get();

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibration.verifications.pdf', compact('verifications', 'plantCode', 'request'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Hasil_Verifikasi_' . date('Ymd_His') . '.pdf');
    }

    public function verificationsQrPdf($id)
    {
        $verification = CalibrationVerification::with('tool')->findOrFail($id);
        $plantCode = $verification->plant ? $verification->plant->code : 'jakarta';

        // Prepare data for QR Code
        $data = "CALIBRATION VERIFICATION REPORT\n";
        $data .= "-------------------------------\n";
        $data .= "Nama Alat: " . $verification->name_alat . "\n";
        $data .= "Merk: " . $verification->merk . "\n";
        $data .= "Serial Number: " . $verification->serial_number . "\n";
        $data .= "Rentang Ukur: " . $verification->rentang_ukur . "\n";
        $data .= "Resolusi: " . $verification->resolusi . "\n";
        $data .= "Lokasi: " . $verification->lokasi_penyimpanan . "\n";
        $data .= "Tgl Kalibrasi: " . ($verification->tanggal_kalibrasi ? \Carbon\Carbon::parse($verification->tanggal_kalibrasi)->format('d/m/Y') : '-') . "\n";
        $data .= "Tgl Verifikasi: " . ($verification->tanggal_verifikasi ? \Carbon\Carbon::parse($verification->tanggal_verifikasi)->format('d/m/Y') : '-') . "\n";
        $data .= "Next Kalibrasi: " . ($verification->next_kalibrasi ? \Carbon\Carbon::parse($verification->next_kalibrasi)->format('d/m/Y') : '-') . "\n";

        $data .= "\nHASIL PENGUKURAN:\n";
        if (is_array($verification->nilai_alat)) {
            foreach ($verification->nilai_alat as $index => $nilai) {
                $data .= "- Nilai: $nilai, Koreksi: " . ($verification->nilai_koreksi[$index] ?? '-') .
                    ", Hasil: " . ($verification->hasil_verifikasi[$index] ?? '-') . "\n";
            }
        }

        $data .= "\nJUDGMENT: " . $verification->judgment . "\n";
        $data .= "-------------------------------\n";
        $data .= "QC IPP - " . date('Y');

        // Generate QR Code with library's base64 helper (Now using PNG)
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'addQuietzone' => true,
            'outputBase64' => true,
            'imageTransparent' => false,
        ]);
        $qrCodeDataUrl = (new QRCode($options))->render($data);

        // Strip the data URL prefix "data:image/png;base64," for existing template compatibility
        $qrCode = $qrCodeDataUrl;
        if (strpos($qrCodeDataUrl, ',') !== false) {
            $qrCode = substr($qrCodeDataUrl, strpos($qrCodeDataUrl, ',') + 1);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibration.verifications.qr_pdf', compact('verification', 'plantCode', 'qrCode'))
            ->setPaper('a4', 'portrait');

        $safeSerialNumber = str_replace(['/', '\\'], '_', $verification->serial_number);
        return $pdf->stream('QR_Verification_' . $safeSerialNumber . '_' . date('Ymd_His') . '.pdf');
    }

    public function verificationsQrData($id)
    {
        try {
            $verification = CalibrationVerification::with('tool')->findOrFail($id);

            // Public download URL (for scanning)
            // Use route() which is more reliable for subfolders, but allow override if needed
            $downloadUrl = route('public.calibration.download', ['id' => $verification->id]);

            $baseUrl = env('QR_BASE_URL');
            if ($baseUrl) {
                $baseUrl = rtrim($baseUrl, '/');
                // If QR_BASE_URL is set, we replace the scheme/host/port part of the route
                $parsedRoute = parse_url($downloadUrl);
                $path = $parsedRoute['path'] ?? '';
                $downloadUrl = $baseUrl . $path;
            }

            \Illuminate\Support\Facades\Log::info('QR Generated for Verification: ' . $verification->id . ' | URL: ' . $downloadUrl);

            // Generate QR Code using library's base64 output (Now using PNG)
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_H,
                'addQuietzone' => true,
                'outputBase64' => true,
                'imageTransparent' => false,
            ]);
            $qrCodeDataUrl = (new QRCode($options))->render($downloadUrl);

            // Strip prefix for frontend compatibility
            $qrCodeBase64 = $qrCodeDataUrl;
            if (strpos($qrCodeDataUrl, ',') !== false) {
                $qrCodeBase64 = substr($qrCodeDataUrl, strpos($qrCodeDataUrl, ',') + 1);
            }

            return response()->json([
                'verification' => $verification,
                'qr_code' => $qrCodeBase64,
                'download_url' => $downloadUrl
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('QR Data Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to generate QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function publicVerificationsDownload($id)
    {
        $verification = CalibrationVerification::with(['tool', 'plant'])->findOrFail($id);
        $plantCode = $verification->plant ? $verification->plant->code : 'jakarta';

        // 1. Generate Laporan Hasil (Halaman 1)
        // Kita gunakan data yang sama seperti verificationsQrPdf tetapi link QR di hal 1 dibuang atau tetap ada?
        // Untuk memudahkan, kita generate Hal 1 dulu.
        $dataReport = "CALIBRATION VERIFICATION REPORT\n";
        // ... (data string QR yang sebelumnya) ...
        // Sebenarnya kita tidak perlu QR Code di dalam PDF yang didownload via QR (agar tidak rekursif/bingung)
        // Kita buat Hal 1 tanpa QR Code dominan atau gunakan template yang ada.

        // Public download URL
        $downloadUrl = route('public.calibration.download', ['id' => $verification->id]);

        $baseUrl = env('QR_BASE_URL');
        if ($baseUrl) {
            $baseUrl = rtrim($baseUrl, '/');
            $parsedRoute = parse_url($downloadUrl);
            $path = $parsedRoute['path'] ?? '';
            $downloadUrl = $baseUrl . $path;
        }

        // Generate Hal 1 QR Code using PNG
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'addQuietzone' => true,
            'outputBase64' => true,
            'imageTransparent' => false,
        ]);
        $qrCodeDataUrlHal1 = (new QRCode($options))->render($downloadUrl);

        $qrCodeHal1 = $qrCodeDataUrlHal1;
        if (strpos($qrCodeDataUrlHal1, ',') !== false) {
            $qrCodeHal1 = substr($qrCodeDataUrlHal1, strpos($qrCodeDataUrlHal1, ',') + 1);
        }

        $pdfReport = \Barryvdh\DomPDF\Facade\Pdf::loadView('calibration.verifications.qr_pdf', [
            'verification' => $verification,
            'plantCode' => $plantCode,
            'qrCode' => $qrCodeHal1
        ])->setPaper('a4', 'portrait');

        $reportContent = $pdfReport->output();

        // 2. Jika tidak ada sertifikat, kirim Hal 1 saja
        if (!$verification->certification_path) {
            $safeSerialNumber = str_replace(['/', '\\'], '_', $verification->serial_number);
            return response($reportContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="Verification_Report_' . $safeSerialNumber . '.pdf"');
        }

        // 3. Gabungkan Hal 1 dan Sertifikat (Halaman 2+) menggunakan FPDI
        $pdfMerge = new Fpdi();

        // Simpan sementara Hal 1
        $tempReportPath = tempnam(sys_get_temp_dir(), 'report_') . '.pdf';
        file_put_contents($tempReportPath, $reportContent);

        // Tambahkan Halaman dari Laporan QC
        $pageCount = $pdfMerge->setSourceFile($tempReportPath);
        for ($n = 1; $n <= $pageCount; $n++) {
            $tplIdx = $pdfMerge->importPage($n);
            $pdfMerge->AddPage();
            $pdfMerge->useTemplate($tplIdx);
        }

        // Tambahkan Halaman dari Sertifikat Asli
        $certificatePath = storage_path('app/public/' . $verification->certification_path);
        if (file_exists($certificatePath)) {
            try {
                $certPageCount = $pdfMerge->setSourceFile($certificatePath);
                for ($n = 1; $n <= $certPageCount; $n++) {
                    $tplIdx = $pdfMerge->importPage($n);
                    $pdfMerge->AddPage();
                    $pdfMerge->useTemplate($tplIdx);
                }
            } catch (\Exception $e) {
                // Jika sertifikat gagal di-import (misal versi PDF tidak kompatibel), kita skip atau tetap kirim Hal 1?
                // Untuk sekarang kita lanjut saja dengan Hal 1 yang sudah ada.
            }
        }

        $safeSerialNumber = str_replace(['/', '\\'], '_', $verification->serial_number);
        $finalOutput = $pdfMerge->Output('S', 'Laporan_Lengkap_' . $safeSerialNumber . '.pdf');

        // Clean up temp file
        @unlink($tempReportPath);

        return response($finalOutput)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Laporan_Lengkap_' . $safeSerialNumber . '.pdf"');
    }



    public function verificationsStore(Request $request)
    {
        try {
            if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
                abort(403, 'Unauthorized action.');
            }
            try {
                $this->validateFileUpload($request, 'certification');

                $request->validate([
                    'tool_id' => 'required|exists:calibration_tools,id',
                    'name_alat' => 'required|string',
                    'merk' => 'required|string',
                    'serial_number' => 'required|string',
                    'rentang_ukur' => 'required|string',
                    'resolusi' => 'required|string',
                    'frekuensi_kalibrasi' => 'required|string',
                    'lokasi_penyimpanan' => 'required|string',
                    'tanggal_kalibrasi' => 'required|date',
                    'tanggal_verifikasi' => 'required|date',
                    'next_kalibrasi' => 'required|date',
                    'nilai_alat' => 'nullable|array',
                    'nilai_koreksi' => 'nullable|array',
                    'nilai_ketidakpastian' => 'nullable|array',
                    'hasil_verifikasi' => 'nullable|array',
                    'judgment' => 'required|string',
                    'std_toleransi' => 'required|string',
                    'acuan_toleransi' => 'required|string',
                    'certification' => 'nullable|sometimes|file|mimes:pdf|max:10240',
                    'plant' => 'required|string',
                ]);
            } catch (ValidationException $e) {
                session()->flash('modal', 'create');
                throw $e;
            }

            $plant = Plant::where('code', $request->plant)->firstOrFail();

            $data = $request->except(['certification', 'plant', '_token']);
            $data['plant_id'] = $plant->id;

            if ($request->hasFile('certification')) {
                if (!Storage::disk('public')->exists('calibration/verifications')) {
                    Storage::disk('public')->makeDirectory('calibration/verifications');
                }

                $file = $request->file('certification');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('calibration/verifications', $filename, 'public');
                $data['certification_path'] = $path;
            }

            CalibrationVerification::create($data);

            // Update tool's schedule planning
            $tool = CalibrationTool::find($request->tool_id);
            if ($tool) {
                $tool->update(['schedule_planning' => $request->next_kalibrasi]);

                // Sync next_kalibrasi to schedules table if it doesn't exist
                $exists = $tool->schedules()->where('schedule_date', $request->next_kalibrasi)->exists();
                if (!$exists) {
                    $tool->schedules()->create(['schedule_date' => $request->next_kalibrasi]);
                }
            }

            return redirect()->route('calibration.verifications.index', ['plant' => $request->plant])
                ->with('success', 'Data Verifikasi berhasil disimpan.');

        } catch (ValidationException $e) {
            \Illuminate\Support\Facades\Log::warning('Validation Failed:', $e->errors());
            session()->flash('modal', 'create');
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Calibration Store Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Gagal menyimpan data ke database: ' . $e->getMessage());
        }
    }

    public function verificationsEdit($id, Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $verification = CalibrationVerification::findOrFail($id);
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $tools = CalibrationTool::where('plant_id', $plant->id)->with('schedules')->get();

        if ($request->ajax()) {
            return response()->json([
                'verification' => $verification,
                'tools' => $tools,
                'plantCode' => $plantCode
            ]);
        }

        return view('calibration.verifications.edit', compact('verification', 'tools', 'plantCode'));
    }

    public function verificationsUpdate(Request $request, $id)
    {
        try {
            if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
                abort(403, 'Unauthorized action.');
            }
            try {
                $this->validateFileUpload($request, 'certification');

                $request->validate([
                    'tool_id' => 'required|exists:calibration_tools,id',
                    'name_alat' => 'required|string',
                    'merk' => 'required|string',
                    'serial_number' => 'required|string',
                    'rentang_ukur' => 'required|string',
                    'resolusi' => 'required|string',
                    'frekuensi_kalibrasi' => 'required|string',
                    'lokasi_penyimpanan' => 'required|string',
                    'tanggal_kalibrasi' => 'required|date',
                    'tanggal_verifikasi' => 'required|date',
                    'next_kalibrasi' => 'required|date',
                    'nilai_alat' => 'nullable|array',
                    'nilai_koreksi' => 'nullable|array',
                    'nilai_ketidakpastian' => 'nullable|array',
                    'hasil_verifikasi' => 'nullable|array',
                    'judgment' => 'required|string',
                    'std_toleransi' => 'required|string',
                    'acuan_toleransi' => 'required|string',
                    'certification' => 'nullable|sometimes|file|mimes:pdf|max:10240',
                    'plant' => 'required|string',
                ]);
            } catch (ValidationException $e) {
                session()->flash('modal', 'edit');
                session()->flash('edit_id', $id);
                throw $e;
            }

            $verification = CalibrationVerification::findOrFail($id);
            $data = $request->except(['certification', 'plant', '_token', '_method']);

            if ($request->hasFile('certification')) {
                if (!Storage::disk('public')->exists('calibration/verifications')) {
                    Storage::disk('public')->makeDirectory('calibration/verifications');
                }

                // Delete old file
                if ($verification->certification_path) {
                    Storage::disk('public')->delete($verification->certification_path);
                }

                $file = $request->file('certification');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('calibration/verifications', $filename, 'public');
                $data['certification_path'] = $path;
            }

            $verification->update($data);

            // Update tool's schedule planning if next_kalibrasi is later
            $tool = CalibrationTool::find($request->tool_id);
            if ($tool) {
                $tool->update(['schedule_planning' => $request->next_kalibrasi]);

                // Sync next_kalibrasi to schedules table if it doesn't exist
                $exists = $tool->schedules()->where('schedule_date', $request->next_kalibrasi)->exists();
                if (!$exists) {
                    $tool->schedules()->create(['schedule_date' => $request->next_kalibrasi]);
                }
            }

            return redirect()->route('calibration.verifications.index', ['plant' => $request->plant])
                ->with('success', 'Data Verifikasi berhasil diperbarui.');

        } catch (ValidationException $e) {
            \Illuminate\Support\Facades\Log::warning('Validation Failed (Update):', $e->errors());
            session()->flash('modal', 'edit');
            session()->flash('edit_id', $id);
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Calibration Update Error (ID: ' . $id . '): ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Gagal memperbarui data di database: ' . $e->getMessage());
        }
    }

    public function verificationsDestroy($id, Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $verification = CalibrationVerification::findOrFail($id);

        if ($verification->certification_path) {
            Storage::disk('public')->delete($verification->certification_path);
        }

        $verification->delete();

        return redirect()->route('calibration.verifications.index', ['plant' => $request->get('plant', 'jakarta')])
            ->with('success', 'Data Verifikasi berhasil dihapus.');
    }

    public function updatePr(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|exists:calibration_tools,id',
            'pr_number' => 'nullable|string',
        ]);

        // Find or create a schedule record for this tool in the current year
        $schedule = \App\Models\CalibrationToolSchedule::where('tool_id', $request->tool_id)
            ->whereYear('schedule_date', date('Y'))
            ->first();

        if (!$schedule) {
            $schedule = \App\Models\CalibrationToolSchedule::create([
                'tool_id' => $request->tool_id,
                'schedule_date' => now()->toDateString(),
            ]);
        }

        if (empty($request->pr_number)) {
            $schedule->pr_number = null;
            $schedule->pr_date = null;
        } else {
            if ($schedule->pr_number !== $request->pr_number) {
                $schedule->pr_number = $request->pr_number;
                $schedule->pr_date = now();
            }
        }

        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'PR berhasil diperbarui.',
            'pr_date' => $schedule->pr_date ? \Carbon\Carbon::parse($schedule->pr_date)->format('d/m/Y') : '-'
        ]);
    }

    private function validateFileUpload(Request $request, $key)
    {
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            if (!$file->isValid()) {
                $error = $file->getError();
                $message = $file->getErrorMessage();

                if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
                    $message = "File size exceeds server limit (upload_max_filesize: " . ini_get('upload_max_filesize') . ").";
                }

                throw ValidationException::withMessages([$key => $message]);
            }
        }
    }
}
