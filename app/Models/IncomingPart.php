<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingPart extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter, \App\Traits\HasDeleteNotification;

    protected $table = 'incoming_parts';

    protected $fillable = [
        'plant_id',
        'item_id',
        'date',
        'shift',
        'lot_qty',
        'total_check',
        'tanggal_datang',
        'judgment',
        'defects',
        'total_ng',
        'operator_initials',
        'remarks',
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
        'date' => 'date',
        'tanggal_datang' => 'date',
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
