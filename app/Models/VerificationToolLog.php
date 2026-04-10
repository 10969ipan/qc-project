<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationToolLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'verification_tool_id',
        'problem_type',
        'description',
        'reported_date',
        'action_taken',
        'judgment_status',
        'judgment_remarks',
        'judged_by',
        'judged_at',
        'user_id',
        'evidence_report',
        'evidence_judgment',
    ];

    protected $casts = [
        'reported_date' => 'date',
        'judged_at' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(VerificationTool::class, 'verification_tool_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function judge()
    {
        return $this->belongsTo(User::class, 'judged_by');
    }
}
