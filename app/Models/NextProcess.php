<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextProcess extends Model
{
    protected $fillable = ['name', 'plant_id', 'module', 'order', 'is_active'];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
