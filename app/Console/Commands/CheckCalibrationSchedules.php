<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckCalibrationSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-calibration-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check upcoming calibration schedules and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\NotificationService $notificationService)
    {
        $this->info('Checking calibration schedules...');
        $notificationService->notifyCalibrationReminder();
        $this->info('Calibration check completed.');
    }
}
