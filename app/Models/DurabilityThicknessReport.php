<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurabilityThicknessReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        // Cascade delete: when DATA 1 (is_trial=false) is deleted,
        // also delete its paired DATA 2 and any legacy lot_no-paired records
        static::deleting(function (self $report) {
            if (!$report->is_trial) {
                // Explicit pairing via data1_id
                static::where('data1_id', $report->id)->delete();

                // Legacy fallback: rows created before data1_id existed
                static::where('is_trial', true)
                    ->whereNull('data1_id')
                    ->where('standard_performance_test_id', $report->standard_performance_test_id)
                    ->where('lot_no', $report->lot_no)
                    ->delete();
            }
        });
    }

    public function standard()
    {
        return $this->belongsTo(StandardPerformanceTest::class, 'standard_performance_test_id');
    }

    public function standardPerformanceTest()
    {
        return $this->belongsTo(StandardPerformanceTest::class, 'standard_performance_test_id');
    }

    public function analis()
    {
        return $this->belongsTo(User::class, 'analis_id');
    }

    public function analisCorrodkote()
    {
        return $this->belongsTo(User::class, 'analis_corrodkote_id');
    }

    public function analisCass()
    {
        return $this->belongsTo(User::class, 'analis_cass_id');
    }

    public function analisSaltSpray()
    {
        return $this->belongsTo(User::class, 'analis_salt_spray_id');
    }

    public function analisPorecount()
    {
        return $this->belongsTo(User::class, 'analis_porecount_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}