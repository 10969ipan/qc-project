<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    protected $fillable = [
        'type',
        'number',
        'status',
        'description',
        'created_by'
    ];
}
