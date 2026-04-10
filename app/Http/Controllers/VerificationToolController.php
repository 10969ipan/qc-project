<?php

namespace App\Http\Controllers;

use App\Models\VerificationTool;
use App\Models\VerificationSchedule;
use App\Models\VerificationVerification;
use App\Models\VerificationToolLog;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;

class VerificationToolController extends Controller
{
    public function scheduleIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $year = $request->input('year', date('Y'));
        
        $query = VerificationTool::where('plant_id', $plant->id)
            ->with(['schedules' => function($q) use ($year) {
                $q->where('year', $year);
            }])
            ->where('status', '!=', 'BROKEN');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%")
                  ->orWhere('customer', 'LIKE', "%{$search}%");
            });
        }

        $tools = $query->get();

        return view('verifications.schedule.index', compact('tools', 'plantCode', 'year'));
    }

    public function toolsIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        // Dropdown options for filters
        $toolTypes = VerificationTool::where('plant_id', $plant->id)->whereNotNull('tool_type')->distinct()->pluck('tool_type');
        $customers = VerificationTool::where('plant_id', $plant->id)->whereNotNull('customer')->distinct()->pluck('customer');
        $verificationTypes = ['INTERNAL', 'EXTERNAL'];

        $query = VerificationTool::where('plant_id', $plant->id)
            ->withCount('verifications');

        // Apply filters matching the Checkshet/In-Process logic style
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('tool_type')) {
            $query->where('tool_type', $request->tool_type);
        }
        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }
        if ($request->filled('verification_type')) {
            $query->where('verification_type', $request->verification_type);
        }
        if ($request->filled('drawing')) {
            $query->where('drawing', $request->drawing);
        }
        if ($request->filled('judgment')) {
            $query->where(function($q) use ($request) {
                if ($request->judgment === 'BELUM') {
                    $q->whereNull('tool_judgment');
                } else {
                    $q->where('tool_judgment', $request->judgment);
                }
            });
        }
        if ($request->filled('tool_status')) {
            $query->where('tool_status', $request->tool_status);
        }

        $tools = $query->orderBy('name_part')->get();

        // Get filter options (Dynamic)
        $toolTypes = VerificationTool::where('plant_id', $plant->id)->distinct()->pluck('tool_type')->filter()->values();
        $customers = VerificationTool::where('plant_id', $plant->id)->distinct()->pluck('customer')->filter()->values();
        $verificationTypes = ['INTERNAL', 'EXTERNAL'];
        $drawings = ['ADA', 'TIDAK ADA'];
        $judgments = ['BELUM', 'OK', 'NG'];
        $statuses = ['AKTIF', 'TIDAK AKTIF'];

        return view('verifications.tools.index', compact(
            'tools', 'plantCode', 'toolTypes', 'customers', 
            'verificationTypes', 'drawings', 'judgments', 'statuses'
        ));
    }

    public function toolsStore(Request $request)
    {
        $request->validate([
            'plant' => 'required|string',
            'name_part' => 'required|string',
            'no_part' => 'required|string',
            'tool_type' => 'required|string',
            'customer' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'verification_frequency' => 'nullable|string',
            'verification_type' => 'nullable|string',
        ]);

        $plant = Plant::where('code', $request->plant)->first();

        $tool = VerificationTool::create(array_merge($request->all(), ['plant_id' => $plant->id]));
        
        ActivityLogger::log('created', $tool, "Menambahkan Master Data Alat Verifikasi: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil disimpan.');
    }

    public function toolsEdit($id)
    {
        $tool = VerificationTool::findOrFail($id);
        return response()->json($tool);
    }

    public function toolsUpdate(Request $request, $id)
    {
        $tool = VerificationTool::findOrFail($id);
        $tool->update($request->all());
        
        ActivityLogger::log('updated', $tool, "Memperbarui Master Data Alat Verifikasi: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil diperbarui.');
    }

    public function toolsDestroy($id)
    {
        $tool = VerificationTool::findOrFail($id);
        $name = $tool->name_part;
        $tool->delete();
        
        ActivityLogger::log('deleted', null, "Menghapus Master Data Alat Verifikasi: {$name}");

        return redirect()->back()->with('success', 'Data alat verifikasi berhasil dihapus.');
    }

    public function verificationsIndex(Request $request)
    {
        $plantCode = $request->input('plant', auth()->user()->plant ? auth()->user()->plant->code : 'jakarta');
        $plant = Plant::where('code', $plantCode)->first();

        $query = VerificationVerification::where('plant_id', $plant->id)
            ->with('tool');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_part', 'LIKE', "%{$search}%")
                  ->orWhere('no_part', 'LIKE', "%{$search}%");
            });
        }

        $verifications = $query->latest('tanggal_verifikasi')->get();

        return view('verifications.verifications.index', compact('verifications', 'plantCode'));
    }

    public function verificationsStore(Request $request)
    {
        $request->validate([
            'tool_id' => 'required|exists:verification_tools,id',
            'tanggal_verifikasi' => 'required|date',
            'judgment' => 'required|string',
        ]);

        $tool = VerificationTool::findOrFail($request->tool_id);

        $verification = VerificationVerification::create(array_merge($request->all(), [
            'name_part' => $tool->name_part,
            'no_part' => $tool->no_part,
            'plant_id' => $tool->plant_id,
        ]));

        // Update tool judgment
        $tool->update(['tool_judgment' => $request->judgment]);

        // Update schedule if exists
        $date = Carbon::parse($request->tanggal_verifikasi);
        $month = $date->month;
        $week = (int)ceil($date->day / 7);
        if ($week > 4) $week = 4;

        VerificationSchedule::updateOrCreate(
            [
                'tool_id' => $tool->id,
                'year' => $date->year,
                'month' => $month,
                'week' => $week,
            ],
            [
                'actual_status' => $request->judgment,
                'actual_date' => $request->tanggal_verifikasi,
            ]
        );

        ActivityLogger::log('created', $verification, "Input Verifikasi Alat: {$tool->name_part}");

        return redirect()->back()->with('success', 'Data verifikasi berhasil disimpan.');
    }
}
