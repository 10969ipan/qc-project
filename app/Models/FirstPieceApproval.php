<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirstPieceApproval extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'first_piece_approvals';

    protected $fillable = [
        'plant_id',
        'item_id',
        'user_id',
        'sap_code',
        'created_at',
        'date',
        'shift',
        'code_machine',
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'part_weight',
        'remarks',
        'next_proses',
        'dimension_check',
        'defects',
        'approval_status',
        'kashift_qc',
        'kashift_approved_at',
        'supervisor_qc',
        'supervisor_approved_at',
        'asst_manager_qc',
        'asst_manager_approved_at',
        'manager_qc',
        'manager_approved_at',
        'cycle_time',
        'rejection_remarks',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_check' => 'array',
        'part_weight' => 'array',
        'date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the plant that owns the checksheet.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
