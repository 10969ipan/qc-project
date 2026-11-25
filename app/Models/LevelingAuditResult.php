<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelingAuditResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_id',
        'check_item_id',
        'prinsip',
        'level',
        'point_check',
        'result',
        'evidence_path',
        'evidence_paths',
        'evidence_note',
        'ng_status',
        'why_1',
        'why_2',
        'why_3',
        'root_cause',
        'corrective_action',
        'corrective_evidence_path',
        'corrective_evidence_paths',
        'corrective_date',
        'corrected_by',
        'verified_by',
        'verified_at',
        'reject_reason',
    ];

    protected $casts = [
        'level'                     => 'integer',
        'evidence_paths'            => 'array',
        'corrective_evidence_paths' => 'array',
        'corrective_date'           => 'date',
        'verified_at'               => 'datetime',
    ];

    public function getEvidencePathsList(): array
    {
        if (is_array($this->evidence_paths) && count($this->evidence_paths) > 0) {
            return array_values(array_filter($this->evidence_paths));
        }
        return $this->evidence_path ? [$this->evidence_path] : [];
    }

    public function getCorrectiveEvidencePathsList(): array
    {
        if (is_array($this->corrective_evidence_paths) && count($this->corrective_evidence_paths) > 0) {
            return array_values(array_filter($this->corrective_evidence_paths));
        }
        return $this->corrective_evidence_path ? [$this->corrective_evidence_path] : [];
    }

    public function audit()
    {
        return $this->belongsTo(LevelingAudit::class, 'audit_id');
    }

    public function checkItem()
    {
        return $this->belongsTo(LevelingCheckItem::class, 'check_item_id');
    }

    public function corrector()
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
