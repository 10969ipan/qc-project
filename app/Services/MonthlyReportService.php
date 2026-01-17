<?php

namespace App\Services;

use App\Models\MonthlyReport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class MonthlyReportService extends BaseService
{
    /**
     * Get all monthly reports
     */
    public function getAllReports()
    {
        return MonthlyReport::with('creator')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Create monthly report
     */
    public function createReport(array $data, ?UploadedFile $pdfFile): MonthlyReport
    {
        DB::beginTransaction();
        try {
            $path = null;
            if ($pdfFile) {
                $filename = time() . '_' . $pdfFile->getClientOriginalName();
                $path = $pdfFile->storeAs('monthly_reports', $filename, 'public');
            }

            if (!empty($data['is_active'])) {
                MonthlyReport::where('is_active', true)->update(['is_active' => false]);
            }

            $report = MonthlyReport::create(array_merge($data, [
                'file_path' => $path,
                'created_by' => auth()->id(),
            ]));

            DB::commit();
            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update monthly report
     */
    public function updateReport(int $id, array $data, ?UploadedFile $pdfFile): MonthlyReport
    {
        DB::beginTransaction();
        try {
            $report = MonthlyReport::findOrFail($id);

            if ($pdfFile) {
                // Delete old file
                if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
                    Storage::disk('public')->delete($report->file_path);
                }
                // Upload new file
                $filename = time() . '_' . $pdfFile->getClientOriginalName();
                $path = $pdfFile->storeAs('monthly_reports', $filename, 'public');
                $report->file_path = $path;
            }

            if (!empty($data['is_active']) && !$report->is_active) {
                MonthlyReport::where('is_active', true)->update(['is_active' => false]);
            }

            $report->update($data);

            DB::commit();
            return $report;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete monthly report
     */
    public function deleteReport(int $id): bool
    {
        DB::beginTransaction();
        try {
            $report = MonthlyReport::findOrFail($id);
            if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
                Storage::disk('public')->delete($report->file_path);
            }
            $result = $report->delete();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Set report as active
     */
    public function setActive(int $id): void
    {
        DB::transaction(function () use ($id) {
            MonthlyReport::where('is_active', true)->update(['is_active' => false]);
            $report = MonthlyReport::findOrFail($id);
            $report->is_active = true;
            $report->save();
        });
    }
}
