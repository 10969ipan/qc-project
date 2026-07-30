<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPlantFilter;

class CustomerClaimRecord extends Model
{
    use HasPlantFilter;

    protected $fillable = [
        'tanggal_claim',
        'customer',
        'plant_up_customer',
        'claim_type',
        'eksternal_internal',
        'no_report',
        'source_type',
        'project',
        'nama_part',
        'problem',
        'kategori_defect',
        'kategori_penyimpangan',
        'qty',
        'initial_operator',
        'initial_inspektor',
        'action_taken',
        'total_akomodasi',
        'total_overtime',
        'total_irregular',
        'total_cost',
        'feedback',
        'status_feedback',
        'status_cm',
        'attachments',
        'monitoring',
        'evaluasi',
        'monitoring_status',
        'plant_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal_claim' => 'date:Y-m-d',
        'total_akomodasi' => 'decimal:2',
        'total_overtime' => 'decimal:2',
        'total_irregular' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'qty' => 'integer',
        'attachments' => 'array',
    ];

    /**
     * Relationship to Plant (IPP Plant)
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Relationship to User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mutator to ensure fields are stored in UPPERCASE
     */
    public function setAttribute($key, $value)
    {
        $uppercaseFields = [
            'customer',
            'plant_up_customer',
            'claim_type',
            'eksternal_internal',
            'no_report',
            'source_type',
            'project',
            'nama_part',
            'kategori_penyimpangan',
            'initial_operator',
            'initial_inspektor',
            'status_feedback',
            'evaluasi',
        ];

        if (in_array($key, $uppercaseFields) && is_string($value)) {
            $value = strtoupper($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get formatted evaluasi date (dd-mm-yyyy).
     */
    public function getEvaluasiFormattedAttribute()
    {
        if (empty($this->evaluasi)) {
            return '-';
        }

        $val = trim($this->evaluasi);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return \Carbon\Carbon::parse($val)->format('d-m-Y');
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $val)) {
            return $val;
        }

        try {
            if (strtotime($val)) {
                return date('d-m-Y', strtotime($val));
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return $val;
    }
}
