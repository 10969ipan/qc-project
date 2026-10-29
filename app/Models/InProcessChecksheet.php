<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InProcessChecksheet extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'in_process_checksheets';

    protected $fillable = [
        'plant_id',
        'user_id',
        'item_id',
        'qrcode',
        'part_code',
        'supplier_id',
        'quantity',
        'unique_code_id',
        'sap_code',
        'scan_method',
        'created_at',
        'date',
        'shift',
        'code_machine', // New field
        'total_qty',
        'sampling_qty',
        'total_ok',
        'total_ng',
        'judgment',
        'operator_initials',
        'remarks',
        'next_proses',
        'tujuan',
        'dimension_check', // New field
        'alat_ukur',
        'part_weight', // Added for AHM
        'defects', // JSON or serialized
        'approval_status',
        'kashift_qc',
        'kashift_approved_at',
        'supervisor_qc',
        'supervisor_approved_at',
        'asst_manager_qc',
        'asst_manager_approved_at',
        'manager_qc',
        'manager_approved_at',
        'cycle_time',
        'rejection_remarks',
        'has_dimension',
        'is_dimension_ng',
        'leader_dimension_check',
        'leader_dimension_user_id',
        'leader_dimension_at',
        'leader_alat_ukur',
        'approval_methods',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_check' => 'array',
        'leader_dimension_check' => 'array',
        'leader_dimension_at' => 'datetime',
        'approval_methods' => 'array',
        'part_weight' => 'array',
        'date' => 'date',
        'has_dimension' => 'boolean',
        'is_dimension_ng' => 'boolean',
    ];

    public function leaderUser()
    {
        return $this->belongsTo(User::class, 'leader_dimension_user_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the plant that owns the checksheet.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAlatUkurArrayAttribute()
    {
        $plantId = $this->plant_id ?? ($this->relationLoaded('item') && $this->item ? $this->item->plant_id : null);
        $rawTools = [];

        if (!empty($this->alat_ukur)) {
            if (is_array($this->alat_ukur)) {
                $rawTools = $this->alat_ukur;
            } else {
                $decoded = json_decode($this->alat_ukur, true);
                if (is_array($decoded)) {
                    $rawTools = $decoded;
                } else {
                    $rawTools = ['1' => (string) $this->alat_ukur];
                }
            }
        } elseif ($this->relationLoaded('item') && $this->item && !empty($this->item->dimension_standards)) {
            // Fallback: Jika data checksheet lama belum memiliki alat_ukur,
            // ambil secara dinamis dari dimension_standards Master Item terkait
            $standards = $this->item->dimension_standards;
            if (is_string($standards)) {
                $standards = json_decode($standards, true);
            }
            if (is_array($standards)) {
                foreach ($standards as $index => $std) {
                    if (is_array($std) && !empty($std['alat_ukur'])) {
                        $pt = !empty($std['point']) ? (int) $std['point'] : ($index + 1);
                        $rawTools[(string) $pt] = trim((string) $std['alat_ukur']);
                    }
                }
            }
        }

        if (empty($rawTools)) {
            return [];
        }

        // Resolusi dinamis nama alat ukur + nomor seri sesuai plant checksheet (O(1) memory cached)
        $resolved = [];
        foreach ($rawTools as $pt => $tool) {
            $resolved[(string)$pt] = MeasuringTool::resolveDisplayName($tool, $plantId);
        }

        return $resolved;
    }

    public function getAlatUkurFormattedAttribute()
    {
        $tools = $this->alat_ukur_array;
        if (empty($tools)) {
            return '-';
        }
        $filtered = array_filter($tools, function ($val) {
            return $val !== null && trim((string)$val) !== '';
        });
        if (empty($filtered)) {
            return '-';
        }
        $unique = array_unique(array_values($filtered));
        if (count($unique) === 1) {
            return reset($unique);
        }
        $parts = [];
        foreach ($filtered as $pt => $tool) {
            $parts[] = "P{$pt}: {$tool}";
        }
        return implode(', ', $parts);
    }
}
