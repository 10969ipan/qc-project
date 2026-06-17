<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DurabilityPlatingChecksheet extends Model
{
    use \App\Traits\HasUuid, \App\Traits\HasPlantFilter, \App\Traits\HasChecksheetApproval, \App\Traits\HasDeleteNotification;

    protected $fillable = [
        'plant_id',
        'date',
        'tanggal_produksi',
        'shift',
        'thickness_standard_id',
        'no_lot_produksi',
        'thickness_cr',
        'thickness_ni',
        'thickness_cu',
        'step_test_sb',
        'step_test_mp',
        'result',
        'analis',
        'keterangan',
        'status',
        'approval_status',
        'operator_name',
        'leader_name',
        'supervisor_name',
        'manager_name',
        'leader_approved_at',
        'supervisor_approved_at',
        'manager_approved_at',
        'rejected_by',
        'reject_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'tanggal_produksi' => 'date',
    ];

    public function standard()
    {
        return $this->belongsTo(ThicknessStandard::class, 'thickness_standard_id');
    }

    public function getModelClass(): string
    {
        return self::class;
    }
}
