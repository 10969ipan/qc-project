<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelingAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_code',
        'audit_date',
        'inspector_id',
        'bagian',
        'item_id',
        'part_name',
        'part_number',
        'auditor_id',
        'auditor_name',
        'total_ok',
        'total_ng',
        'score',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'total_ok'   => 'integer',
        'total_ng'   => 'integer',
        'score'      => 'float',
    ];

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function results()
    {
        return $this->hasMany(LevelingAuditResult::class, 'audit_id');
    }
}
