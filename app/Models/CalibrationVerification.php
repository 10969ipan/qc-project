<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalibrationVerification extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter, \App\Traits\HasUuid;

    protected $fillable = [
        'tool_id',
        'name_alat',
        'merk',
        'serial_number',
        'rentang_ukur',
        'resolusi',
        'frekuensi_kalibrasi',
        'lokasi_penyimpanan',
        'tanggal_kalibrasi',
        'tanggal_verifikasi',
        'next_kalibrasi',
        'nilai_alat',
        'nilai_koreksi',
        'nilai_ketidakpastian',
        'hasil_verifikasi',
        'judgment',
        'std_toleransi',
        'acuan_toleransi',
        'certification_path',
        'plant_id',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
        'tanggal_verifikasi' => 'date',
        'next_kalibrasi' => 'date',
        'nilai_alat' => 'array',
        'nilai_koreksi' => 'array',
        'nilai_ketidakpastian' => 'array',
        'hasil_verifikasi' => 'array',
    ];

    public function tool()
    {
        return $this->belongsTo(CalibrationTool::class, 'tool_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
