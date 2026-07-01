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

    public function analis()
    {
        return $this->belongsTo(User::class, 'analis_id');
    }
}