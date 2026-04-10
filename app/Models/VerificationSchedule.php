<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tool_id',
        'year',
        'month',
        'week',
        'planning_status',
        'actual_status',
        'actual_date',
    ];

    public function tool()
    {
        return $this->belongsTo(VerificationTool::class, 'tool_id');
    }
}
