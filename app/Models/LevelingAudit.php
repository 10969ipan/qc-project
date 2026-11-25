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
        'meja_mesin',
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

    /**
     * Format gabungan Proses / Line / Meja / Mesin (contoh: "Inspector Plating / Meja 9").
     */
    public function getProsesLineLabelAttribute(): string
    {
        $bagian = trim((string) ($this->bagian ?? ''));
        $meja = trim((string) ($this->meja_mesin ?? ''));

        if ($bagian !== '' && $meja !== '') {
            return $bagian . ' / ' . $meja;
        }
        if ($bagian !== '') {
            return $bagian;
        }
        return $meja !== '' ? $meja : '-';
    }
}
