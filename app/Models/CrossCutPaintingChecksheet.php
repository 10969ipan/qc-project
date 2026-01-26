<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossCutPaintingChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'cross_cut_painting_checksheets';

    protected $fillable = [
        'plant_id',
        'item_id',
        'production_shift',
        'qc_shift',
        'production_datetime',
        'qc_datetime',
        'image_path',
        'pencil_scratch',
        'tap_test',
        'position_remark_judgment',
        'keterangan',
        'next_proses',
        'defects',
        'total_ng',
        'sampling_qty',
        'cycle_time',
        'operator_initials',
        'approval_status',
        'karu_qc',
        'karu_qc_approved_at',
        'kashift_qc',
        'kashift_approved_at',
        'kashift_plating',
        'kashift_plating_approved_at',
        'supervisor_qc',
        'supervisor_approved_at',
        'supervisor_plating',
        'supervisor_plating_approved_at',
        'asst_manager_qc',
        'asst_manager_approved_at',
        'manager_qc',
        'manager_approved_at',
        'manager_plating',
        'manager_plating_approved_at',
        'rejection_remarks',
    ];

    protected $casts = [
        'defects' => 'array',
        'production_datetime' => 'datetime',
        'qc_datetime' => 'datetime',
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
