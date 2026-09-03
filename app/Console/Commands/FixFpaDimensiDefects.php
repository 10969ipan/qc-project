<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FirstPieceApproval;
use App\Services\FirstPieceApprovalService;

class FixFpaDimensiDefects extends Command
{
    protected $signature = 'fpa:reevaluate {--id= : Re-evaluate specific FPA ID}';
    protected $description = 'Re-evaluate FPA Checksheets to fix stale NG Dimensi defects and update judgment/total_ok/total_ng in DB';

    protected $service;

    public function __construct(FirstPieceApprovalService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $id = $this->option('id');
        $query = FirstPieceApproval::query();

        if ($id) {
            $query->where('id', $id);
        }

        $checksheets = $query->get();
        $this->info("Found " . $checksheets->count() . " FPA checksheets to re-evaluate.");

        $updatedCount = 0;
        foreach ($checksheets as $checksheet) {
            $oldJudgment = $checksheet->judgment;
            $oldTotalNg = $checksheet->total_ng;
            
            $dimensions = is_array($checksheet->dimension_check) 
                ? $checksheet->dimension_check 
                : json_decode($checksheet->dimension_check, true) ?? [];

            $defects = is_array($checksheet->defects)
                ? $checksheet->defects
                : json_decode($checksheet->defects, true) ?? [];

            // Calculate base NG count without Dimensi defects
            $isDimensiType = function($t) {
                $key = strtolower(trim((string)$t));
                return in_array($key, ['dimensi', 'dimension', 'ng dimensi']);
            };

            $baseTotalNg = 0;
            foreach ($defects as $d) {
                if (is_array($d) && !$isDimensiType($d['type'] ?? '')) {
                    $baseTotalNg += (int)($d['qty'] ?? 0);
                }
            }

            $dataToValidate = [
                'dimensions' => $dimensions,
                'total_ng' => $baseTotalNg,
            ];

            $validated = $this->service->validateDimensions($dataToValidate, $checksheet->item_id);
            $newJudgment = $validated['judgment'] ?? $oldJudgment;

            // Call syncNgDimensiDefect using reflection or updating directly
            $refMethod = new \ReflectionMethod(\App\Http\Controllers\FirstPieceApprovalController::class, 'syncNgDimensiDefect');
            $refMethod->setAccessible(true);
            $controller = app(\App\Http\Controllers\FirstPieceApprovalController::class);

            $checksheet = $refMethod->invoke(
                $controller,
                $checksheet,
                $oldJudgment,
                $newJudgment,
                $validated['ok_points_count'] ?? null,
                $validated['ng_points_count'] ?? null
            );

            if ($checksheet->isDirty()) {
                $checksheet->save();
                $updatedCount++;
                $this->info("Updated FPA #{$checksheet->id}: Judgment ({$oldJudgment} -> {$checksheet->judgment}), Total NG ({$oldTotalNg} -> {$checksheet->total_ng})");
            }
        }

        $this->info("Done! $updatedCount checksheets updated.");
        return 0;
    }
}
