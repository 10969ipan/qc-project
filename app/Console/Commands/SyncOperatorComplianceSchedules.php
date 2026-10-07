<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OperatorComplianceChecksheet;
use App\Models\OperatorComplianceSchedule;

class SyncOperatorComplianceSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:sync-compliance-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi riwayat entri checksheet kepatuhan operator yang sudah ada agar memiliki record Plan (P) di tabel jadwal.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi jadwal kepatuhan operator...');

        $checksheets = OperatorComplianceChecksheet::with(['operator', 'entries'])->get();

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($checksheets as $cs) {
            if (!$cs->user_id) {
                continue;
            }

            $days = $cs->entries->pluck('day')->unique()->filter()->values();
            if ($days->isEmpty()) {
                continue;
            }

            $plant = strtolower($cs->plant_code ?: 'karawang');
            $bagian = $cs->bagian ?: 'Quality Control';

            foreach ($days as $day) {
                $dateStr = sprintf('%04d-%02d-%02d', $cs->year, $cs->month, (int)$day);

                $exists = OperatorComplianceSchedule::where('plant', $plant)
                    ->where('operator_id', $cs->user_id)
                    ->whereDate('schedule_date', $dateStr)
                    ->first();

                if ($exists) {
                    $skippedCount++;
                } else {
                    OperatorComplianceSchedule::create([
                        'plant'         => $plant,
                        'operator_id'   => $cs->user_id,
                        'bagian'        => $bagian,
                        'shift'         => 'Non Shift',
                        'schedule_date' => $dateStr,
                    ]);
                    $createdCount++;
                    $operatorName = $cs->operator ? $cs->operator->name : "Operator #{$cs->user_id}";
                    $this->line(" [OK] Dibuat Plan untuk {$operatorName} pada tanggal {$dateStr} ({$bagian})");
                }
            }
        }

        $this->info("Sinkronisasi Selesai! Berhasil membuat {$createdCount} jadwal Plan baru. Dilewati {$skippedCount} jadwal yang sudah ada.");
        return 0;
    }
}
