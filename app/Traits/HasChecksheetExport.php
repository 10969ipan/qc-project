<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

trait HasChecksheetExport
{
    /**
     * Define headers for CSV and Google Sheets
     */
    abstract protected function getExportHeaders();

    /**
     * Map a single checksheet row to array for export
     */
    abstract protected function mapExportRow($checksheet);

    /**
     * Must be implemented by Controller to return the Model class name
     */
    abstract protected function getModelClass();

    public function syncToGoogleSheets(Request $request)
    {
        try {
            $service = app(GoogleSheetService::class);
            // Optional: Set sheet name if controller defines it
            if (method_exists($this, 'getGoogleSheetName')) {
                $service->setSheetName($this->getGoogleSheetName());
            }

            $service->clearSheet();

            // Headers
            $service->appendRows([$this->getExportHeaders()]);

            $modelClass = $this->getModelClass();
            $count = 0;

            // Chunking
            $modelClass::with('item')
                ->orderBy('date')
                ->orderBy('created_at')
                ->chunk(500, function ($checksheets) use ($service, &$count) {
                    $rows = [];
                    foreach ($checksheets as $c) {
                        $rows[] = $this->mapExportRow($c);
                    }
                    $service->appendRows($rows);
                    $count += count($rows);
                });

            return redirect()->back()->with('success', 'Sinkronisasi ke Google Sheets berhasil (' . $count . ' data).');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Sinkronisasi: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $modelClass = $this->getModelClass();
        $query = $modelClass::with('item')->orderBy('date', 'desc')->orderBy('created_at', 'desc');

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Allow controller to add extra filters if needed
        if (method_exists($this, 'applyExportFilters')) {
            $this->applyExportFilters($query, $request);
        }

        $checksheets = $query->get();
        $filename = "export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = $this->getExportHeaders();

        $callback = function () use ($checksheets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($checksheets as $checksheet) {
                fputcsv($file, $this->mapExportRow($checksheet));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
