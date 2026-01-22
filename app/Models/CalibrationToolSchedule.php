<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationToolSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'schedule_date',
        'pr_number',
        'pr_date',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'pr_date' => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(CalibrationTool::class, 'tool_id');
    }
}
