<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatingChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'plating_checksheets';

    protected $fillable = [
        'plant_id',
        'item_id',
        'qrcode',
        'part_code',
        'supplier_id',
        'quantity',
        'unique_code_id',
        'sap_code',
        'created_at',
        'date',
        'shift',
        'line',
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
        'injection_date',
        'injection_shift',
        'plating_date',
        'plating_shift',
        'no_lot',
        'standard_cycle_time',
    ];

    protected $casts = [
        'defects' => 'array',
        'date' => 'date',
        'injection_date' => 'date',
        'plating_date' => 'date',
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
