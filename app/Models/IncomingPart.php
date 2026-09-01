<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingPart extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'incoming_parts';

    protected $fillable = [
        'plant_id',
        'item_id',
        'arrival_id',
        'date',
        'shift',
        'lot_qty',
        'total_check',
        'sampling_qty',
        'qty_balance_sisa',
        'tanggal_datang',
        'judgment',
        'defects',
        'total_ng',
        'operator_initials',
        'remarks',
        'part_code',
        'supplier_id',
        'quantity',
        'unique_code_id',
        'sap_code',
        'scan_method',
        'qrcode',
        'cycle_time',
        'approval_status',
        'kashift_qc',
        'supervisor_qc',
        'asst_manager_qc',
        'manager_qc',
        'kashift_approved_at',
        'supervisor_approved_at',
        'asst_manager_approved_at',
        'manager_approved_at',
        'rejection_remarks',
    ];

    protected $casts = [
        'defects' => 'array',
        'date' => 'date:Y-m-d',
        'tanggal_datang' => 'date:Y-m-d',
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

    public function arrival()
    {
        return $this->belongsTo(IncomingPartArrival::class, 'arrival_id');
    }
}
