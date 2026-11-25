<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelingCheckItem extends Model
{
    use HasFactory;

    public const DEFAULT_PRINSIP_ORDER = ['MSK', 'KT', 'PNG', 'PSSP', 'KAO'];

    protected $fillable = [
        'prinsip',
        'level',
        'order_no',
        'point_check',
        'guidance',
        'is_active',
    ];

    protected $casts = [
        'level'     => 'integer',
        'order_no'  => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function results()
    {
        return $this->hasMany(LevelingAuditResult::class, 'check_item_id');
    }
}
