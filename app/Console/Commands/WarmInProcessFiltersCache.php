<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plant;
use App\Models\Item;
use App\Models\InProcessChecksheet;
use App\Models\GeneralSetting;
use App\Services\InProcessChecksheetService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmInProcessFiltersCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'in-process:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memanaskan (pre-warm) cache filter dropdown index in-process checksheet agar semua user membukanya instan';

    /**
     * Execute the console command.
     */
    public function handle(InProcessChecksheetService $service)
    {
        $this->info('Memulai warming cache in-process...');

        // 1. Warm consolidated standards
        Cache::put('in_proc_standards', $service->getConsolidatedStandards(), 43200);
        $this->line('✓ Standards dimension cached.');

        // 2. Warm per-plant filters
        $plants = Plant::all();
        foreach ($plants as $plant) {
            $plantId = $plant->id;

            // General settings
            $plantCodeVal = $plantId ?? 'global';
            $settings = GeneralSetting::whereIn('key', [
                'hidden_items_inprocess',
                'hide_ng_rows_inprocess',
                'hide_no_dimension_rows_inprocess',
            ])
            ->where(function ($q) use ($plantCodeVal) {
                $q->where('plant_code', $plantCodeVal)
                  ->orWhere('plant_code', 'global');
            })
            ->get()
            ->keyBy('key');
            Cache::put("in_proc_settings_{$plantCodeVal}", $settings, 1800);

            // Filter Items
            $items = Item::select('id', 'name', 'part_number', 'customer')
                ->whereExists(function ($sub) use ($plantId) {
                    $sub->select(DB::raw(1))
                        ->from('in_process_checksheets')
                        ->whereColumn('in_process_checksheets.item_id', 'items.id')
                        ->where('in_process_checksheets.plant_id', $plantId);
                })->orderBy('name')->get();
            Cache::put("in_proc_filter_items_v8_{$plantId}", $items, 43200);

            // Filter Customers
            $customers = $items->pluck('customer')
                ->filter(fn($v) => !empty($v))
                ->unique()
                ->sort()
                ->values();
            Cache::put("in_proc_filter_cust_v7_{$plantId}", $customers, 43200);

            // Filter Initials (90 hari terakhir)
            $initials = InProcessChecksheet::where('plant_id', $plantId)
                ->where('date', '>=', now()->subDays(90))
                ->whereNotNull('operator_initials')
                ->where('operator_initials', '!=', '')
                ->distinct()
                ->pluck('operator_initials')
                ->sort();
            Cache::put("in_proc_filter_init_v2_{$plantId}", $initials, 1800);

            // Filter Machines
            $machines = InProcessChecksheet::where('plant_id', $plantId)
                ->whereNotNull('code_machine')
                ->where('code_machine', '!=', '')
                ->distinct()
                ->pluck('code_machine')
                ->sort(SORT_NUMERIC)
                ->values();
            Cache::put("in_proc_filter_mach_v2_{$plantId}", $machines, 3600);

            $this->line("✓ Plant {$plant->name} ({$plant->code}) cache warmed.");
        }

        $this->info('Semua cache in-process berhasil dipanaskan!');
        return 0;
    }
}
