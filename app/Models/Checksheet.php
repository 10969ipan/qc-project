<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checksheet extends Model
{
    use HasFactory;

    protected $table = 'checksheets';

    protected $fillable = [
        'item_id',
        'created_at',
        'date',
        'shift',
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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
