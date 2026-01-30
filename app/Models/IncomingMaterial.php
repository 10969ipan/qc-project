<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingMaterial extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'incoming_materials';

    protected $fillable = [
        'plant_id',
        'item_id',
        'standard',
        'tanggal_datang',
        'date',
        'lot_batch_number',
        'quantity_kg',
        'komper_karung_kg',
        'sampling_size_karung_kg',
        'expired_date',
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
        'expired_date' => 'date',
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
