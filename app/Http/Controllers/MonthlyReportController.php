<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use App\Services\MonthlyReportService;
use App\Http\Requests\StoreMonthlyReportRequest;
use App\Http\Requests\UpdateMonthlyReportRequest;

class MonthlyReportController extends Controller
{
    protected $reportService;

    public function __construct(MonthlyReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display a listing of monthly reports
     */
    public function index()
    {
        $reports = $this->reportService->getAllReports();
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
    public function store(StoreMonthlyReportRequest $request)
    {
        try {
            $this->reportService->createReport($request->validated(), $request->file('pdf_file'));
            return redirect()->route('admin.monthly-reports.index')
                ->with('success', 'Laporan bulanan berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan laporan: ' . $e->getMessage());
        }
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
    public function update(UpdateMonthlyReportRequest $request, $id)
    {
        try {
            $this->reportService->updateReport($id, $request->validated(), $request->file('pdf_file'));
            return redirect()->route('admin.monthly-reports.index')
                ->with('success', 'Laporan bulanan berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified monthly report
     */
    public function destroy($id)
    {
        try {
            $this->reportService->deleteReport($id);
            return redirect()->route('admin.monthly-reports.index')
                ->with('success', 'Laporan bulanan berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
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
        try {
            $this->reportService->setActive($id);
            return redirect()->route('admin.monthly-reports.index')
                ->with('success', 'Laporan berhasil diatur sebagai laporan aktif di dashboard!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengatur laporan aktif: ' . $e->getMessage());
        }
    }
}
