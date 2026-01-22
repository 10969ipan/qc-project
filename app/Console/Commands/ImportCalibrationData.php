<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CalibrationTool;
use App\Models\CalibrationToolSchedule;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportCalibrationData extends Command
{
    protected $signature = 'import:calibration {file}';
    protected $description = 'Import calibration tools and schedules from CSV';

    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $plant = Plant::where('code', 'karawang')->first();
        if (!$plant) {
            $this->error("Karawang plant not found");
            return 1;
        }

        $handle = fopen($file, "r");
        if (!$handle) {
            $this->error("Could not open file");
            return 1;
        }

        $this->info("Importing tools to {$plant->name} plant...");

        // Skip headers (rows 1-10)
        for ($i = 0; $i < 10; $i++)
            fgetcsv($handle);

        $currentSection = '';
        $importCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows or summary rows
            if (empty($row[2]) && empty($row[1]))
                continue;
            if (str_contains($row[0] ?? '', 'TOTAL'))
                break;

            // Handle Section (BAGIAN)
            if (!empty($row[1])) {
                $currentSection = trim($row[1]);
            }

            $no = $row[0];
            $name = trim($row[2]);
            $serial = trim($row[3]);
            $range = trim($row[4]);
            $resolusi = trim($row[5]);
            $lokasi = trim($row[6]);
            $tglBeli = $this->parseDate($row[7]);
            $frekuensi = trim($row[8]);
            $riwayat = $row[9];
            $jenis = trim($row[13]);

            if (empty($name))
                continue;

            // Handle Schedule Planning (Natural Date detection or Calculation)
            $schedulePlanning = null;
            preg_match('/\((.*?)\)/', $frekuensi, $matches);
            if (isset($matches[1])) {
                $schedulePlanning = $this->parseNaturalDate($matches[1]);
            }

            // Fallback for schedule_planning if still null (required by DB)
            if (!$schedulePlanning && $tglBeli) {
                $schedulePlanning = $this->calculateNextSchedule($tglBeli, $frekuensi);
            }

            // Ultimate fallback to satisfy DB constraints
            if (!$schedulePlanning)
                $schedulePlanning = now()->format('Y-m-d');

            $tool = CalibrationTool::updateOrCreate(
                [
                    'serial_number' => $serial ?: 'N/A-' . uniqid(),
                    'plant_id' => $plant->id
                ],
                [
                    'bagian' => $currentSection ?: 'QC',
                    'name_alat' => $name,
                    'range' => $range ?: '-',
                    'resolusi' => $resolusi ?: '-',
                    'lokasi_pakai' => $lokasi ?: '-',
                    'tanggal_beli' => $tglBeli ?: now()->format('Y-m-d'),
                    'frekuensi_kalibrasi' => $frekuensi ?: '1 Tahun',
                    'riwayat_kalibrasi' => $riwayat,
                    'jenis_kalibrasi' => $jenis ?: 'Eksternal',
                    'schedule_planning' => $schedulePlanning,
                ]
            );

            // Handle Schedules Table (Detailed logic)
            $this->processSchedules($tool, $frekuensi, $schedulePlanning, $row);

            $importCount++;
        }

        fclose($handle);
        $this->info("Successfully imported $importCount tools.");
        return 0;
    }

    private function calculateNextSchedule($baseDate, $freqStr)
    {
        try {
            $date = Carbon::parse($baseDate);
            if (stripos($freqStr, 'Tahun') !== false) {
                preg_match('/\d+/', $freqStr, $matches);
                $years = (int) ($matches[0] ?? 1);
                return $date->addYears($years)->format('Y-m-d');
            }
            if (stripos($freqStr, 'Bulan') !== false) {
                return $date->addMonth()->format('Y-m-d');
            }
        } catch (\Exception $e) {
        }
        return now()->format('Y-m-d');
    }

    private function parseDate($dateString)
    {
        if (empty($dateString) || $dateString == '-')
            return null;
        try {
            return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('m/d/Y', $dateString)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    private function processSchedules($tool, $frekuensi, $schedulePlanning, $row)
    {
        // Clear existing schedules for this tool to prevent duplicates (starting clean)
        $tool->schedules()->delete();

        // 1. If frequency is "1 Bulan", schedule for every month of 2026
        if (stripos($frekuensi, '1 Bulan') !== false) {
            for ($m = 1; $m <= 12; $m++) {
                CalibrationToolSchedule::create([
                    'tool_id' => $tool->id,
                    'schedule_date' => Carbon::create(2026, $m, 1)->format('Y-m-d')
                ]);
            }
        }

        // 2. If we found a specific natural date (e.g. June 2027)
        if ($schedulePlanning) {
            CalibrationToolSchedule::create([
                'tool_id' => $tool->id,
                'schedule_date' => $schedulePlanning
            ]);
        }
    }

    private function parseNaturalDate($str)
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'Mei' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Agust' => 8,
            'Sept' => 9,
            'Okt' => 10,
            'Nov' => 11,
            'Des' => 12,
            'Maret' => 3,
            'Agustus' => 8,
            'Sep' => 9,
            'Oktober' => 10,
            'Desember' => 12
        ];

        foreach ($months as $name => $num) {
            if (stripos($str, $name) !== false) {
                preg_match('/\d{4}/', $str, $yearMatch);
                $year = $yearMatch[0] ?? 2026;
                return Carbon::create($year, $num, 1)->format('Y-m-d');
            }
        }
        return null;
    }
}
