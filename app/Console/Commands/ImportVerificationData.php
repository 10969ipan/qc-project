<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\VerificationTool;
use App\Models\VerificationSchedule;
use App\Models\Plant;
use Illuminate\Support\Str;

class ImportVerificationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-verification-data {file=SCHEDULE VERIFIKASI JIG, MAL, CF.xlsx} {--plant=jakarta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import verification data from Excel file (Sheet 2026 NEW)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $plantCode = $this->option('plant');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $plant = Plant::where('code', $plantCode)->first();
        if (!$plant) {
            $this->error("Plant with code $plantCode not found.");
            return 1;
        }

        $this->info("Loading spreadsheet...");
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('2026 NEW');

        if (!$sheet) {
            $this->error("Sheet '2026 NEW' not found.");
            return 1;
        }

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // Find header row "NO, NAMA PART, NO. PART"
        $headerRow = 0;
        for ($row = 1; $row <= 20; $row++) {
            $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
            $rowString = implode(' ', array_filter($rowValues));
            if (stripos($rowString, 'NO') !== false && stripos($rowString, 'NAMA PART') !== false && stripos($rowString, 'NO. PART') !== false) {
                $headerRow = $row;
                break;
            }
        }

        if (!$headerRow) {
            $this->error("Header row not found in sheet '2026 NEW'.");
            return 1;
        }

        $this->info("Found Header at row: $headerRow. Importing data...");

        // Data starts 3 rows after header (Header, Months, Weeks)
        $dataStartRow = $headerRow + 3;
        $year = 2026;

        $toolCounter = 0;

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
            
            $namaPart = $rowData[2] ?? null; // C
            $noPart = $rowData[3] ?? null;   // D

            if (empty($namaPart) && empty($noPart)) {
                $this->warn("Skipping empty row $row");
                continue;
            }

            $toolCounter++;
            $drawing = $toolCounter <= 18 ? 'ADA' : 'TIDAK ADA';
            $status = $toolCounter <= 18 ? 'AKTIF' : 'TIDAK AKTIF';

            $tool = VerificationTool::updateOrCreate(
                [
                    'name_part' => (string)($namaPart ?? '-'),
                    'no_part' => (string)($noPart ?? '-'),
                    'plant_id' => $plant->id,
                ],
                [
                    'tool_type' => $rowData[4] ?? '-',
                    'customer' => $rowData[5] ?? '-',
                    'quantity' => (int)($rowData[6] ?? 0),
                    'verification_frequency' => $rowData[7] ?? '-',
                    'calibration_history' => $rowData[8] ?? '-',
                    'verification_type' => $rowData[9] ?? '-',
                    'drawing' => $drawing,
                    'tool_status' => $status,
                    'tool_judgment' => null, // Reset judgment
                ]
            );

            // Import schedules (weekly)
            // Column 10 is usually where weeks start in "2026 NEW"
            // Let's dynamically find it by looking for "Jan" or week "1" in rows above data
            $weekStartCol = 10;
            
            for ($col = $weekStartCol; $col < $highestColIndex; $col++) {
                $status = $rowData[$col] ?? null;
                if (!$status) continue;

                // Determine month and week from header rows
                // Row $headerRow+1 has month
                // Row $headerRow+2 has week
                $monthStr = null;
                // Move backwards from $col to find the last non-empty month cell
                for ($mCol = $col; $mCol >= $weekStartCol; $mCol--) {
                    $mVal = $sheet->getCell([$mCol + 1, $headerRow + 1])->getValue();
                    if ($mVal) {
                        $monthStr = $mVal;
                        break;
                    }
                }

                $weekNum = $sheet->getCell([$col + 1, $headerRow + 2])->getValue();
                
                if (!$monthStr || !$weekNum) continue;

                $monthMap = [
                    'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                    'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12,
                    'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
                    'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
                ];

                $month = $monthMap[$monthStr] ?? null;
                if (!$month) continue;

                VerificationSchedule::updateOrCreate(
                    [
                        'tool_id' => $tool->id,
                        'year' => $year,
                        'month' => $month,
                        'week' => (int)$weekNum,
                    ],
                    [
                        'planning_status' => (stripos($status, 'P') !== false) ? 'P' : null,
                        'actual_status' => (stripos($status, 'A') !== false || stripos($status, 'OK') !== false) ? $status : null,
                    ]
                );
            }

            if ($row % 50 == 0) {
                $this->info("Imported $row rows...");
            }
        }

        $this->info("Import completed successfully.");
        return 0;
    }
}
