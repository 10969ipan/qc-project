<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model buat nampung catatan temuan masalah abnormal, tindakan perbaikan, PIC & statusnya
 */
class OperatorComplianceProblem extends Model
{
    use HasFactory;

    // Kolom-kolom yang bisa diisi masal
    protected $fillable = [
        'checksheet_id',
        'item_id',
        'problem_date',
        'problem_description',
        'corrective_action',
        'pic_name',
        'target_date',
        'status',
        'created_by',
    ];

    // Format tanggal otomatis jadi instance Carbon (date)
    protected $casts = [
        'problem_date' => 'date',
        'target_date'  => 'date',
    ];

    // Relasi balik ke header checksheet
    public function checksheet()
    {
        return $this->belongsTo(OperatorComplianceChecksheet::class, 'checksheet_id');
    }

    // Relasi ke item audit master yang terhubung
    public function item()
    {
        return $this->belongsTo(OperatorComplianceItem::class, 'item_id');
    }

    // Relasi ke user yang nginput data masalah ini
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
