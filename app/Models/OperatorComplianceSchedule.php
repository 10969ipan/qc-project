<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorComplianceSchedule extends Model
{
    protected $fillable = [
        'plant',
        'operator_id',
        'bagian',
        'shift',
        'schedule_date',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
