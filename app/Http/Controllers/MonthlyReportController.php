<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyReportController extends Controller
{
    /**
     * Display a listing of monthly reports
     */
    public function index()
    {
        $reports = MonthlyReport::with('creator')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.monthly_reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new monthly report
     */
    public function create()
    {
        return view('admin.monthly_reports.create');
    }

    /**
     * Store a newly created monthly report
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Handle PDF upload
        if ($request->hasFile('pdf_file')) {
            $file = $request->file('pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('monthly_reports', $filename, 'public');

            // If "set as active" is checked, deactivate all other reports
            $isActive = $request->has('is_active');
            if ($isActive) {
                MonthlyReport::where('is_active', true)->update(['is_active' => false]);
            }

            // Create the report
            MonthlyReport::create([
                'month' => $request->month,
                'year' => $request->year,
                'title' => $request->title,
                'file_path' => $path,
                'is_active' => $isActive,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.monthly-reports.index')
                ->with('success', 'Laporan bulanan berhasil ditambahkan!');
        }

        return back()->with('error', 'Gagal mengupload file PDF.');
    }

    /**
     * Show the form for editing the specified monthly report
     */
    public function edit($id)
    {
        $report = MonthlyReport::findOrFail($id);
        return view('admin.monthly_reports.edit', compact('report'));
    }

    /**
     * Update the specified monthly report
     */
    public function update(Request $request, $id)
    {
        $report = MonthlyReport::findOrFail($id);

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'title' => 'required|string|max:255',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // Handle PDF replacement
        if ($request->hasFile('pdf_file')) {
            // Delete old file
            if (Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }

            // Upload new file
            $file = $request->file('pdf_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('monthly_reports', $filename, 'public');
            $report->file_path = $path;
        }

        // Update basic info
        $report->month = $request->month;
        $report->year = $request->year;
        $report->title = $request->title;

        // Handle active status
        $isActive = $request->has('is_active');
        if ($isActive && !$report->is_active) {
            MonthlyReport::where('is_active', true)->update(['is_active' => false]);
        }
        $report->is_active = $isActive;

        $report->save();

        return redirect()->route('admin.monthly-reports.index')
            ->with('success', 'Laporan bulanan berhasil diperbarui!');
    }

    /**
     * Remove the specified monthly report
     */
    public function destroy($id)
    {
        $report = MonthlyReport::findOrFail($id);

        // Delete PDF file
        if (Storage::disk('public')->exists($report->file_path)) {
            Storage::disk('public')->delete($report->file_path);
        }

        $report->delete();

        return redirect()->route('admin.monthly-reports.index')
            ->with('success', 'Laporan bulanan berhasil dihapus!');
    }

    /**
     * Serve PDF file for viewing
     */
    public function servePdf($id)
    {
        $report = MonthlyReport::findOrFail($id);
        $path = storage_path('app/public/' . $report->file_path);

        if (!file_exists($path)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($report->file_path) . '"'
        ]);
    }

    /**
     * Set a report as active (to display on dashboard)
     */
    public function setActive($id)
    {
        DB::transaction(function () use ($id) {
            // Deactivate all reports
            MonthlyReport::where('is_active', true)->update(['is_active' => false]);

            // Activate selected report
            $report = MonthlyReport::findOrFail($id);
            $report->is_active = true;
            $report->save();
        });

        return redirect()->route('admin.monthly-reports.index')
            ->with('success', 'Laporan berhasil diatur sebagai laporan aktif di dashboard!');
    }
}
