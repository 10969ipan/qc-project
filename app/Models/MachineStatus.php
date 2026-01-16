<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    use \App\Traits\HasPlantFilter;

    protected $fillable = [
        'plant',
        'type',
        'number',
        'status',
        'description',
        'created_by'
    ];
}
