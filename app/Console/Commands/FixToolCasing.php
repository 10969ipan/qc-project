<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixToolCasing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:tool-casing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix casing of calibration tools data (Title Case)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix casing for calibration tools...');

        $tools = \App\Models\CalibrationTool::all();
        $bar = $this->output->createProgressBar(count($tools));

        $bar->start();

        foreach ($tools as $tool) {
            $tool->name_alat = \Illuminate\Support\Str::title($tool->name_alat);
            $tool->merk = \Illuminate\Support\Str::title($tool->merk);
            $tool->jenis_kalibrasi = \Illuminate\Support\Str::title($tool->jenis_kalibrasi);
            $tool->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Successfully fixed casing for all calibration tools.');
    }
}
