<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoubleTapeChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'double_tape_checksheets';

    protected $fillable = [
        'plant_id',
        'item_id',
        'created_at',
        'date',
        'shift',
        // 'line' is omitted
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'remarks',
        'next_proses',
        'defects', // JSON
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
        'date' => 'date',
        'kashift_approved_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'asst_manager_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
