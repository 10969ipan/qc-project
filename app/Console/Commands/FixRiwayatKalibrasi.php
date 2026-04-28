<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CalibrationTool;

class FixRiwayatKalibrasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:fix-riwayat-kalibrasi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memperbaiki format data riwayat kalibrasi yang sebelumnya tersimpan sebagai tanggal atau format tidak valid menjadi hitungan (Misal: 1 Kali)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pemindaian dan perbaikan data riwayat_kalibrasi...');

        $tools = CalibrationTool::withCount('verifications as all_verifications_count')->get();
        $fixedCount = 0;

        foreach($tools as $tool) {
            $currentRiwayat = $tool->riwayat_kalibrasi;
            
            // Check if it contains a date format (like /) or is empty
            if (strpos((string)$currentRiwayat, '/') !== false || empty($currentRiwayat)) {
                $count = $tool->all_verifications_count;
                $tool->riwayat_kalibrasi = $count . ' Kali';
                $tool->save();
                $fixedCount++;
            } 
            // Check if it's purely a number (e.g., "2" instead of "2 Kali")
            elseif (is_numeric($currentRiwayat)) {
                $tool->riwayat_kalibrasi = $currentRiwayat . ' Kali';
                $tool->save();
                $fixedCount++;
            }
        }

        $this->info("Pembersihan selesai! Berhasil memperbaiki format pada {$fixedCount} data alat ukur.");
        return 0;
    }
}
