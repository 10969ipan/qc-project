<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAssyChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'sub_assy_checksheets';

    protected $fillable = [
        'plant_id',
        'user_id',
        'item_id',
        'created_at',
        'date',
        'shift',
        'line', // New field
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'remarks',
        'next_proses',
        'defects', // JSON or serialized
        'approval_status',
        'kashift_qc',
        'supervisor_qc',
        'asst_manager_qc',
        'manager_qc',
        'manager_approved_at',
        'cycle_time',
        'rejection_remarks',
    ];

    protected $casts = [
        'defects' => 'array',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
