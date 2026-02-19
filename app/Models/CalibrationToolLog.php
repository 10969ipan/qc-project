<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationToolLog extends Model
{
    use HasFactory, \App\Traits\HasUuid;

    protected $fillable = [
        'calibration_tool_id',
        'problem_type',
        'action_taken',
        'description',
        'reported_date',
        'user_id',
        'judgment_status',
        'judgment_remarks',
        'evidence_report',
        'evidence_judgment',
        'judged_by',
        'judged_at',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'judged_at' => 'datetime',
    ];

    public function judgedBy()
    {
        return $this->belongsTo(User::class, 'judged_by');
    }

    public function tool()
    {
        return $this->belongsTo(CalibrationTool::class, 'calibration_tool_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
