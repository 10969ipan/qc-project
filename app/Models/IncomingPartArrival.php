<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingPartArrival extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'incoming_part_arrivals';

    protected $fillable = [
        'plant_id',
        'item_id',
        'tanggal_datang',
        'shift_datang',
        'qty_datang',
        'qty_sisa',
        'status',
    ];

    protected $casts = [
        'tanggal_datang' => 'date',
        'qty_datang' => 'integer',
        'qty_sisa' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function checksheets()
    {
        return $this->hasMany(IncomingPart::class, 'arrival_id');
    }
}
