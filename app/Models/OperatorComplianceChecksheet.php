<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorComplianceChecksheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plant_code',
        'bagian',
        'month',
        'year',
        'leader_checks',
        'spv_checks',
        'leader_checked',
        'leader_checked_at',
        'leader_id',
        'spv_checked',
        'spv_checked_at',
        'spv_id',
        'mgr_checked',
        'mgr_checked_at',
        'mgr_id',
        'created_by',
    ];

    protected $casts = [
        'leader_checks'     => 'array',
        'spv_checks'        => 'array',
        'leader_checked'    => 'boolean',
        'leader_checked_at' => 'datetime',
        'spv_checked'       => 'boolean',
        'spv_checked_at'    => 'datetime',
        'mgr_checked'       => 'boolean',
        'mgr_checked_at'    => 'datetime',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'spv_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'mgr_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries()
    {
        return $this->hasMany(OperatorComplianceEntry::class, 'checksheet_id');
    }

    public function problems()
    {
        return $this->hasMany(OperatorComplianceProblem::class, 'checksheet_id');
    }
}
