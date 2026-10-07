<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model buat header checksheet kepatuhan operator quality per bulan & per plant
 */
class OperatorComplianceChecksheet extends Model
{
    use HasFactory;

    // Kolom-kolom yang bisa diisi masal
    protected $fillable = [
        'user_id',
        'plant_code',
        'bagian',
        'month',
        'year',
        'leader_checks',
        'spv_checks',
        'leader_checked',
        'leader_checked_at',
        'leader_id',
        'spv_checked',
        'spv_checked_at',
        'spv_id',
        'mgr_checked',
        'mgr_checked_at',
        'mgr_id',
        'created_by',
    ];

    // Konversi tipe data otomatis (casting)
    protected $casts = [
        'leader_checks'     => 'array',
        'spv_checks'        => 'array',
        'leader_checked'    => 'boolean',
        'leader_checked_at' => 'datetime',
        'spv_checked'       => 'boolean',
        'spv_checked_at'    => 'datetime',
        'mgr_checked'       => 'boolean',
        'mgr_checked_at'    => 'datetime',
    ];

    // Relasi ke user operator / inspector yang diaudit
    public function operator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Alias relasi user untuk operator
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke user leader / kashift yang meriksa
    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // Relasi ke user supervisor / karu yang verifikasi
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'spv_id');
    }

    // Relasi ke user manager / asst manager yang ketahui
    public function manager()
    {
        return $this->belongsTo(User::class, 'mgr_id');
    }

    // Relasi ke user yang bikin checksheet ini
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke entri matriks harian (OK / NG / NA)
    public function entries()
    {
        return $this->hasMany(OperatorComplianceEntry::class, 'checksheet_id');
    }

    // Relasi ke daftar item masalah / abnormal yang tercatat
    public function problems()
    {
        return $this->hasMany(OperatorComplianceProblem::class, 'checksheet_id');
    }
}
