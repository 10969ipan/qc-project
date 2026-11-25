<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelingRecapApproval extends Model
{
    use HasFactory;

    public const STAGES = [
        'auditor'  => 'Auditor',
        'leader'   => 'Leader/Karu',
        'spv'      => 'SPV',
        'asst_mgr' => 'Asst. Mgr',
    ];

    public const STAGE_ROLES = [
        'auditor'  => ['admin', 'supervisor', 'supervisor_qc', 'supervisor_plating', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'],
        'leader'   => ['admin', 'leader', 'karu_qc', 'karu_prod', 'kashift', 'kashift_plating'],
        'spv'      => ['admin', 'supervisor', 'supervisor_qc', 'supervisor_plating'],
        'asst_mgr' => ['admin', 'asst_manager', 'asst_manager_qc', 'asst_manager_plating', 'manager', 'manager_qc', 'manager_plating'],
    ];

    public const REKAP_ACCESS_ROLES = [
        'admin',
        'leader', 'karu_qc', 'karu_prod', 'kashift', 'kashift_plating',
        'supervisor', 'supervisor_qc', 'supervisor_plating',
        'asst_manager', 'asst_manager_qc', 'asst_manager_plating',
        'manager', 'manager_qc', 'manager_plating',
    ];

    protected $fillable = [
        'plant_code',
        'year',
        'inspector_id',
        'auditor_approved_by',
        'auditor_approved_at',
        'leader_approved_by',
        'leader_approved_at',
        'spv_approved_by',
        'spv_approved_at',
        'asst_mgr_approved_by',
        'asst_mgr_approved_at',
    ];

    protected $casts = [
        'year'                 => 'integer',
        'auditor_approved_at'  => 'datetime',
        'leader_approved_at'   => 'datetime',
        'spv_approved_at'      => 'datetime',
        'asst_mgr_approved_at' => 'datetime',
    ];

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function auditorApprover()
    {
        return $this->belongsTo(User::class, 'auditor_approved_by');
    }

    public function leaderApprover()
    {
        return $this->belongsTo(User::class, 'leader_approved_by');
    }

    public function spvApprover()
    {
        return $this->belongsTo(User::class, 'spv_approved_by');
    }

    public function asstMgrApprover()
    {
        return $this->belongsTo(User::class, 'asst_mgr_approved_by');
    }
}
