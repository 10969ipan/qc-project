<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPlantFilter;
use App\Traits\HasUuid;

class VerificationTool extends Model
{
    use HasFactory, HasPlantFilter, HasUuid, SoftDeletes;

    protected $fillable = [
        'name_part',
        'no_part',
        'tool_type',
        'customer',
        'quantity',
        'verification_frequency',
        'calibration_history',
        'verification_type',
        'drawing',
        'tool_judgment',
        'tool_status',
        'verification_date_remarks',
        'certification_path',
        'plant_id',
        'status',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function schedules()
    {
        return $this->hasMany(VerificationSchedule::class, 'tool_id');
    }

    public function verifications()
    {
        return $this->hasMany(VerificationVerification::class, 'tool_id');
    }

    public function logs()
    {
        return $this->hasMany(VerificationToolLog::class, 'verification_tool_id');
    }

    public function pendingLogs()
    {
        return $this->hasMany(VerificationToolLog::class, 'verification_tool_id')->whereNull('judgment_status');
    }

    public function latestVerification()
    {
        return $this->hasOne(VerificationVerification::class, 'tool_id')->latestOfMany('tanggal_verifikasi');
    }
}
