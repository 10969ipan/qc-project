<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerClaimRecord;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ImportCustomerClaims extends Command
{
    protected $signature = 'import:customer-claims';
    protected $description = 'Import detailed customer claim records from Google Spreadsheet CSV';

    public function handle()
    {
        $csvUrl = "https://docs.google.com/spreadsheets/d/14spFX5RCJkZ6pUyDM-UT-guvXkxvRPDfUZD3knV9J0I/export?format=csv";

        $this->info("Fetching CSV from Google Spreadsheet...");
        $response = Http::withoutVerifying()->get($csvUrl);

        if ($response->failed()) {
            $this->error("Failed to fetch CSV");
            return 1;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($tempFile, $response->body());
        $handle = fopen($tempFile, 'r');

        if (!$handle) {
            $this->error("Failed to open temp file");
            return 1;
        }

        // Skip first 6 rows (metadata and headers)
        for ($i = 0; $i < 6; $i++) {
            fgetcsv($handle);
        }

        $krwPlant = Plant::where('code', 'karawang')->first();
        $jktPlant = Plant::where('code', 'jakarta')->first();
        $adminUser = User::where('role', 'admin')->first();

        if (!$krwPlant || !$jktPlant) {
            $this->error("Karawang or Jakarta plant not found in database!");
            return 1;
        }

        $importCount = 0;
        $this->info("Importing records...");

        $index = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if ($index === 0) {
                $this->info("First row columns: " . count($row));
                $this->info("First column value: '" . $row[0] . "'");
            }
            $index++;

            // Clean row[0] - sometimes Google Sheets CSV has a BOM or spaces
            if (isset($row[0])) {
                $row[0] = trim(preg_replace('/[^0-9]/', '', $row[0]));
            }

            // Skip empty rows or rows without a number
            if (empty($row) || empty($row[0]) || !is_numeric($row[0])) {
                continue;
            }

            try {
                $tanggalStr = $row[1] ?? null;
                $tanggalClaim = $this->parseDate($tanggalStr);

                $plantCode = trim(strtoupper($row[8] ?? ''));
                if ($plantCode === 'JKT' || $plantCode === 'JAKARTA') {
                    $plantId = $jktPlant->id;
                } else {
                    $plantId = $krwPlant->id;
                }

                $costStr = $row[19] ?? '0';
                $totalCost = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', str_replace('Rp', '', $costStr)));

                $dataToInsert = [
                    'tanggal_claim' => $tanggalClaim,
                    'customer' => $row[2] ?? null,
                    'plant_up_customer' => $row[3] ?? null,
                    'claim_type' => $row[4] ?? null,
                    'no_report' => $row[5] ?? null,
                    'source_type' => $row[6] ?? null,
                    'project' => $row[7] ?? null,
                    'nama_part' => $row[9] ?? null,
                    'problem' => $row[10] ?? null,
                    'kategori_defect' => $row[11] ?? null,
                    'kategori_penyimpangan' => $row[12] ?? null,
                    'qty' => (int) ($row[13] ?? 0),
                    'initial_operator' => $row[14] ?? null,
                    'initial_inspektor' => $row[15] ?? null,
                    'frek' => $row[16] ?? null,
                    'persen_frek' => $row[17] ?? null,
                    'action_taken' => $row[18] ?? null,
                    'total_cost' => $totalCost,
                    'feedback' => $row[20] ?? null,
                    'status_feedback' => $row[21] ?? null,
                    'status_cm' => $row[22] ?? null,
                    'monitoring' => $row[23] ?? null,
                    'evaluasi' => $row[24] ?? null,
                    'monitoring_status' => $row[25] ?? null,
                    'plant_id' => $plantId,
                    'created_by' => $adminUser ? $adminUser->id : null,
                ];

                if ($index === 1) { // Log second data row as well
                    $this->info("Data to insert row 7: " . json_encode(array_filter($dataToInsert)));
                }

                CustomerClaimRecord::create($dataToInsert);

                $importCount++;
            } catch (\Exception $e) {
                $this->error("Error importing row " . ($index + 7) . ": " . $e->getMessage());
            }
        }

        $this->info("Successfully imported $importCount records.");
        return 0;
    }

    private function parseDate($dateString)
    {
        if (empty($dateString) || $dateString == '-')
            return null;

        try {
            // Spreadsheet format seems to be d-M-Y or similar (9-Jan-26)
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
