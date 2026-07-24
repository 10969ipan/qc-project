<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurabilityThicknessReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
}