<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IncomingPartArrival;
use App\Models\IncomingPart;

class SyncIncomingScanStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:sync-scan-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi ulang stok kedatangan (mengembalikan pemotongan stok dari transaksi scan QR Code)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi stok kedatangan...');
        $startTime = microtime(true);

        $arrivals = IncomingPartArrival::all();
        $updatedCount = 0;

        foreach ($arrivals as $arrival) {
            // Hitung akumulasi total_check dari transaksi MANUAL saja
            $manualTotalCheck = (int)IncomingPart::where('arrival_id', $arrival->id)
                ->where(function ($q) {
                    $q->whereNull('scan_method')->orWhere('scan_method', 'manual');
                })
                ->sum('total_check');

            $expectedQtySisa = max(0, $arrival->qty_datang - $manualTotalCheck);

            if ($arrival->qty_sisa !== $expectedQtySisa) {
                $oldSisa = $arrival->qty_sisa;
                $arrival->qty_sisa = $expectedQtySisa;
                $arrival->status = ($expectedQtySisa <= 0) ? 'COMPLETED' : 'OPEN';
                $arrival->save();

                // Sync latest checksheet's qty_balance_sisa
                IncomingPart::where('arrival_id', $arrival->id)
                    ->latest('id')
                    ->limit(1)
                    ->update(['qty_balance_sisa' => $expectedQtySisa]);

                $updatedCount++;
                $this->line("  [OK] Lot ID #{$arrival->id}: Sisa stok dipulihkan dari {$oldSisa} -> {$expectedQtySisa} pcs");
            }
        }

        $elapsed = number_format(microtime(true) - $startTime, 2);
        $this->info("Penyelarasan selesai! {$updatedCount} lot stok berhasil dipulihkan dalam {$elapsed} detik.");
        return 0;
    }
}
