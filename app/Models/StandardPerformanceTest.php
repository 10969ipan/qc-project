<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandardPerformanceTest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function reports()
    {
        return $this->hasMany(DurabilityThicknessReport::class, 'standard_performance_test_id');
    }
}
