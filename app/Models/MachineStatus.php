<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStatus extends Model
{
    use \App\Traits\HasPlantFilter;

    protected $fillable = [
        'plant_id',
        'type',
        'number',
        'status',
        'description',
        'created_by'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the plant that owns the status.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    protected static function booted()
    {
        static::saved(fn() => \App\Services\DashboardService::clearDashboardCache());
        static::deleted(fn() => \App\Services\DashboardService::clearDashboardCache());
    }
}
