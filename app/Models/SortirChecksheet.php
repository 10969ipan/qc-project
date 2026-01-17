<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SortirChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $fillable = [
        'plant',
        'item_id',
        'source_type',
        'source_id',
        'date',
        'shift',
        'line',
        'total_qty',
        'sampling_qty',
        'defects',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'remarks',
        'cycle_time',
        'next_proses',
        'kashift_qc',
        'kashift_qc_time',
        'supervisor_qc',
        'supervisor_qc_time',
        'asst_manager_qc',
        'asst_manager_qc_time',
        'manager_qc',
        'manager_qc_time',
        'rejection_remarks',
    ];


    protected $casts = [
        'defects' => 'array',
        'date' => 'date',
        'kashift_qc_time' => 'datetime',
        'supervisor_qc_time' => 'datetime',
        'asst_manager_qc_time' => 'datetime',
        'manager_qc_time' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\Item::class);
    }

    public function sourceChecksheet()
    {
        if ($this->source_type === 'sub_assy') {
            return $this->belongsTo(\App\Models\SubAssyChecksheet::class, 'source_id');
        } elseif ($this->source_type === 'in_process') {
            return $this->belongsTo(\App\Models\InProcessChecksheet::class, 'source_id');
        } elseif ($this->source_type === 'cross_cut') {
            return $this->belongsTo(\App\Models\CrossCutChecksheet::class, 'source_id');
        }
        return null;
    }
}
