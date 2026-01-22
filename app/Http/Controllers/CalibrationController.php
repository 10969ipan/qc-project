<?php

namespace App\Http\Controllers;

use App\Models\CalibrationTool;
use App\Models\CalibrationVerification;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CalibrationController extends Controller
{
    public function scheduleIndex(Request $request)
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
                },
                'latestVerification'
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
                    ->orWhere('jenis_kalibrasi', 'LIKE', "%{$search}%");

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

    public function toolsCreate(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        return view('calibration.tools.create', compact('plantCode'));
    }

    public function toolsStore(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'plant' => 'required|string',
            'bagian' => 'required|string',
            'name_alat' => 'required|string',
            'serial_number' => 'required|string',
            'range' => 'required|string',
            'resolusi' => 'required|string',
            'lokasi_pakai' => 'required|string',
            'tanggal_beli' => 'required|date',
            'frekuensi_kalibrasi' => 'required|string',
            'jenis_kalibrasi' => 'required|string',
            'schedule_planning' => 'required|array',
            'schedule_planning.*' => 'required|date',
            'certification' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $plant = Plant::where('code', $request->plant)->first();

        $data = $request->except(['certification', 'plant', 'schedule_planning']);
        $data['plant_id'] = $plant->id;

        // Use the first schedule as the main schedule_planning for legacy purposes
        $data['schedule_planning'] = $request->schedule_planning[0];

        if ($request->hasFile('certification')) {
            $file = $request->file('certification');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('calibration/tools', $filename, 'public');
            $data['certification_path'] = $path;
        }

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
        return view('calibration.tools.edit', compact('tool', 'plantCode'));
    }

    public function toolsUpdate(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'plant' => 'required|string',
            'bagian' => 'required|string',
            'name_alat' => 'required|string',
            'serial_number' => 'required|string',
            'range' => 'required|string',
            'resolusi' => 'required|string',
            'lokasi_pakai' => 'required|string',
            'tanggal_beli' => 'required|date',
            'frekuensi_kalibrasi' => 'required|string',
            'jenis_kalibrasi' => 'required|string',
            'schedule_planning' => 'required|array',
            'schedule_planning.*' => 'required|date',
            'certification' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $tool = CalibrationTool::findOrFail($id);
        $data = $request->except(['certification', 'plant', 'schedule_planning']);

        // Use the first schedule as the main schedule_planning for legacy purposes
        $data['schedule_planning'] = $request->schedule_planning[0];

        if ($request->hasFile('certification')) {
            // Delete old file if exists
            if ($tool->certification_path) {
                Storage::disk('public')->delete($tool->certification_path);
            }

            $file = $request->file('certification');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('calibration/tools', $filename, 'public');
            $data['certification_path'] = $path;
        }

        $tool->update($data);

        // Sync schedules: Delete all and recreate (simplest way to sync)
        $tool->schedules()->delete();
        foreach ($request->schedule_planning as $date) {
            $tool->schedules()->create(['schedule_date' => $date]);
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

        return view('calibration.verifications.index', compact('verifications', 'plantCode'));
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

    public function verificationsCreate(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action. Managers can only perform approvals.');
        }
        $plantCode = $request->get('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $tools = CalibrationTool::where('plant_id', $plant->id)->with('schedules')->get();
        $selectedToolId = $request->get('tool_id');

        return view('calibration.verifications.create', compact('tools', 'plantCode', 'selectedToolId'));
    }

    public function verificationsStore(Request $request)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
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
            'nilai_alat' => 'required|array',
            'nilai_koreksi' => 'required|array',
            'nilai_ketidakpastian' => 'required|array',
            'hasil_verifikasi' => 'required|array',
            'judgment' => 'required|string',
            'std_toleransi' => 'required|string',
            'acuan_toleransi' => 'required|string',
            'certification' => 'nullable|file|mimes:pdf|max:10240',
            'plant' => 'required|string',
        ]);

        $plant = Plant::where('code', $request->plant)->firstOrFail();

        $data = $request->except(['certification', 'plant', '_token']);
        $data['plant_id'] = $plant->id;

        if ($request->hasFile('certification')) {
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
        }

        return redirect()->route('calibration.verifications.index', ['plant' => $request->plant])
            ->with('success', 'Data Verifikasi berhasil disimpan.');
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

        return view('calibration.verifications.edit', compact('verification', 'tools', 'plantCode'));
    }

    public function verificationsUpdate(Request $request, $id)
    {
        if (in_array(auth()->user()->role, ['manager', 'asst_manager'])) {
            abort(403, 'Unauthorized action.');
        }
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
            'nilai_alat' => 'required|array',
            'nilai_koreksi' => 'required|array',
            'nilai_ketidakpastian' => 'required|array',
            'hasil_verifikasi' => 'required|array',
            'judgment' => 'required|string',
            'std_toleransi' => 'required|string',
            'acuan_toleransi' => 'required|string',
            'certification' => 'nullable|file|mimes:pdf|max:10240',
            'plant' => 'required|string',
        ]);

        $verification = CalibrationVerification::findOrFail($id);
        $data = $request->except(['certification', 'plant', '_token', '_method']);

        if ($request->hasFile('certification')) {
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
        }

        return redirect()->route('calibration.verifications.index', ['plant' => $request->plant])
            ->with('success', 'Data Verifikasi berhasil diperbarui.');
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
}
