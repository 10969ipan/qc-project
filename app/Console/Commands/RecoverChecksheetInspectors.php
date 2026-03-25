<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubAssyChecksheet;
use App\Models\InProcessChecksheet;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class RecoverChecksheetInspectors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checksheet:recover-inspectors {--dry-run : Only show what would be updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recover original inspector names from ActivityLog for Sub-Assy and In-Process checksheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting inspector recovery process...');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved.');
        }

        $this->processSubAssy($dryRun);
        $this->processInProcess($dryRun);

        $this->info('Recovery process completed!');
    }

    private function processSubAssy($dryRun)
    {
        $this->info('Processing Sub-Assy Checksheets...');
        $checksheets = SubAssyChecksheet::all();
        $updatedCount = 0;

        foreach ($checksheets as $checksheet) {
            $originalLog = ActivityLog::where('model_type', SubAssyChecksheet::class)
                ->where('model_id', $checksheet->id)
                ->where('action', 'created')
                ->oldest()
                ->first();

            if ($originalLog && $originalLog->user_id != $checksheet->user_id) {
                $this->line("Found difference for Sub-Assy ID {$checksheet->id}: Current User ID {$checksheet->user_id} -> Original User ID {$originalLog->user_id}");
                
                if (!$dryRun) {
                    $checksheet->user_id = $originalLog->user_id;
                    $checksheet->save();
                }
                $updatedCount++;
            }
        }

        $this->info("Sub-Assy recovery: {$updatedCount} records " . ($dryRun ? 'found' : 'updated') . ".");
    }

    private function processInProcess($dryRun)
    {
        $this->info('Processing In-Process Checksheets...');
        $checksheets = InProcessChecksheet::all();
        $updatedCount = 0;

        foreach ($checksheets as $checksheet) {
            $originalLog = ActivityLog::where('model_type', InProcessChecksheet::class)
                ->where('model_id', $checksheet->id)
                ->where('action', 'created')
                ->oldest()
                ->first();

            if ($originalLog && $originalLog->user_id != $checksheet->user_id) {
                $this->line("Found difference for In-Process ID {$checksheet->id}: Current User ID {$checksheet->user_id} -> Original User ID {$originalLog->user_id}");
                
                if (!$dryRun) {
                    $checksheet->user_id = $originalLog->user_id;
                    $checksheet->save();
                }
                $updatedCount++;
            }
        }

        $this->info("In-Process recovery: {$updatedCount} records " . ($dryRun ? 'found' : 'updated') . ".");
    }
}
