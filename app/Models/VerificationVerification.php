<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class VerificationVerification extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'tool_id',
        'name_part',
        'no_part',
        'tanggal_verifikasi',
        'next_verifikasi',
        'judgment',
        'remarks',
        'certification_path',
        'plant_id',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'date',
        'next_verifikasi' => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(VerificationTool::class, 'tool_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }
}
