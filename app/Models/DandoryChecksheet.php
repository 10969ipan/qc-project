<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DandoryChecksheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_code',
        'check_date',
        'shift',
        'table_no',
        'operator_id',
        'jabatan',
        'item_id',
        'part_name',
        'awal_checks',
        'akhir_checks',
        'awal_completed_at',
        'akhir_completed_at',
        'notes',
        'leader_checked',
        'leader_user_id',
        'leader_name',
        'leader_checked_at',
        'is_rejected',
        'rejection_remarks',
        'rejected_by',
        'rejector_name',
        'rejected_at',
        'created_by',
    ];

    protected $casts = [
        'check_date'         => 'date',
        'awal_checks'        => 'array',
        'akhir_checks'       => 'array',
        'awal_completed_at'  => 'datetime',
        'akhir_completed_at' => 'datetime',
        'leader_checked'     => 'boolean',
        'leader_checked_at'  => 'datetime',
        'is_rejected'        => 'boolean',
        'rejected_at'        => 'datetime',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Memeriksa apakah pemeriksaan Dandory Awal dan Akhir sudah diisi lengkap.
     */
    public function isComplete(?int $minAwal = 9, ?int $minAkhir = 10): bool
    {
        $minAwal = $minAwal ?: 9;
        $minAkhir = $minAkhir ?: 10;

        $awalCount = count((array) ($this->awal_checks ?? []));
        $akhirCount = count((array) ($this->akhir_checks ?? []));

        return $awalCount >= $minAwal && $akhirCount >= $minAkhir;
    }
}

