<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingSubPart extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter;

    protected $table = 'incoming_sub_parts';

    protected $fillable = [
        'plant_id',
        'item_id',
        'standard',
        'tanggal_datang',
        'date',
        'lot_batch_number',
        'quantity',
        'sampling_size_pcs',
        'check_dimensi',
        'judgment',
        'defects',
        'total_ng',
        'operator_initials',
        'remarks',
        'cycle_time',
        'approval_status',
        'kashift_qc',
        'supervisor_qc',
        'asst_manager_qc',
        'manager_qc',
        'kashift_approved_at',
        'supervisor_approved_at',
        'asst_manager_approved_at',
        'manager_approved_at',
        'rejection_remarks',
        'alat_ukur',
        'leader_alat_ukur',
        'leader_dimension_check',
        'leader_dimension_user_id',
        'leader_dimension_at',
        'approval_methods',
    ];

    protected $casts = [
        'defects' => 'array',
        'date' => 'date',
        'tanggal_datang' => 'date',
        'kashift_approved_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'asst_manager_approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'leader_dimension_check' => 'array',
        'leader_dimension_at' => 'datetime',
        'approval_methods' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function leaderUser()
    {
        return $this->belongsTo(User::class, 'leader_dimension_user_id');
    }

    public function getDimensionCheckAttribute()
    {
        return $this->attributes['check_dimensi'] ?? null;
    }

    public function getAlatUkurArrayAttribute(): array
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
            // Fallback: Jika data checksheet belum memiliki alat_ukur langsung,
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

    public function getAlatUkurFormattedAttribute(): string
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
