<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InProcessChecksheet;
use App\Services\InProcessChecksheetService;
use Illuminate\Support\Facades\Log;

class ReEvaluateInProcessJudgments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inprocess:reevaluate {--force : Update records without asking} {--id= : Re-evaluate a specific checksheet ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-evaluate OK/NG judgment for In-Process Checksheets based on current dimension standards';

    protected $service;

    public function __construct(InProcessChecksheetService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->option('id');
        $query = InProcessChecksheet::query();

        if ($id) {
            $query->where('id', $id);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->warn("No checksheets found.");
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("Do you want to re-evaluate $count checksheets? This will update the 'judgment' column in the database.", true)) {
            $this->info("Operation cancelled.");
            return 0;
        }

        $this->info("Re-evaluating $count checksheets...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updatedCount = 0;

        $query->chunkById(100, function ($checksheets) use ($bar, &$updatedCount) {
            foreach ($checksheets as $checksheet) {
                // Prepare data for service
                $data = [
                    'dimensions' => is_string($checksheet->dimension_check) ? json_decode($checksheet->dimension_check, true) : $checksheet->dimension_check,
                    'total_ng' => $checksheet->total_ng, // Pass existing NG count from defects
                    'judgment' => $checksheet->judgment,
                ];

                // Re-validate dimension
                $result = $this->service->validateDimensions($data, $checksheet->item_id);

                // If judgment changed, update it
                if ($result['judgment'] !== $checksheet->judgment) {
                    $checksheet->judgment = $result['judgment'];
                    $checksheet->save();
                    $updatedCount++;
                    Log::info("Re-evaluated checksheet #{$checksheet->id}: Changed from {$data['judgment']} to {$result['judgment']}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done! $updatedCount checksheets updated.");

        return 0;
    }
}
