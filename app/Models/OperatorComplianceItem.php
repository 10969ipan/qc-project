<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model buat master item audit kepatuhan operator (dikategoriin sesuai Prinsip Dasar)
 */
class OperatorComplianceItem extends Model
{
    use HasFactory;

    // Role yang boleh mengelola Master Item Audit (SPV ke atas), dipakai controller & view
    public const MASTER_MANAGER_ROLES = [
        'admin',
        'supervisor', 'supervisor_qc', 'supervisor_plating',
        'asst_manager', 'asst_manager_qc', 'asst_manager_plating',
        'manager', 'manager_qc', 'manager_plating',
    ];

    // Kolom-kolom yang bisa diisi masal
    protected $fillable = [
        'bagian',
        'prinsip_dasar',
        'item_check',
        'standard',
        'order_no',
        'is_active',
    ];

    /**
     * Daftar bagian standar untuk Kepatuhan Operator
     */
    public static function getBagianOptions(): array
    {
        return [
            'Inspector Outgoing Plating',
            'Inspector Outgoing Painting',
            'Inspector Outgoing Export',
            'Inspector Outgoing Sub Assy',
            'Inspector Inproses',
            'Inspector Incoming Sub-Part/Material/Chemical',
            'Inspector Incoming Plating',
            'Inspector Outgoing Double Tape',
            'Performance Test',
            'Claim Customer',
            'Final ISD',
            'Digitalisasi Support',
        ];
    }

    /**
     * Ambil daftar item audit aktif yang berlaku untuk bagian tertentu
     * (gabungan Default + pengaturan ON/OFF & override per bagian + item khusus bagian).
     */
    public static function getItemsForBagian(?string $bagian)
    {
        $defaultItems = static::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('bagian')->orWhere('bagian', '');
            })
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        $cleanBagian = trim((string) $bagian);
        if ($cleanBagian === '' || $cleanBagian === 'Quality Control') {
            return collect(static::regroupByPrinsip($defaultItems->all(), fn($it) => $it->prinsip_dasar));
        }

        $settingsByItemId = OperatorComplianceItemBagianSetting::where('bagian', $cleanBagian)
            ->get()
            ->keyBy('item_id');

        $customBagianItems = static::where('is_active', true)
            ->where('bagian', $cleanBagian)
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        $result = collect();

        foreach ($defaultItems as $def) {
            $st = $settingsByItemId->get($def->id);
            if ($st && !$st->is_enabled) {
                continue;
            }

            $it = clone $def;
            if ($st) {
                if ($st->custom_prinsip_dasar !== null && $st->custom_prinsip_dasar !== '') {
                    $it->prinsip_dasar = $st->custom_prinsip_dasar;
                }
                if ($st->custom_item_check !== null && $st->custom_item_check !== '') {
                    $it->item_check = $st->custom_item_check;
                }
                if ($st->custom_standard !== null && $st->custom_standard !== '') {
                    $it->standard = $st->custom_standard;
                }
                if ($st->custom_order_no !== null) {
                    $it->order_no = (int) $st->custom_order_no;
                }
            }
            $result->push($it);
        }

        foreach ($customBagianItems as $cust) {
            $st = $settingsByItemId->get($cust->id);
            if ($st && !$st->is_enabled) {
                continue;
            }
            $result->push($cust);
        }

        $sorted = $result->sort(function ($a, $b) {
            return ((int) $a->order_no <=> (int) $b->order_no)
                ?: ((int) $a->id <=> (int) $b->id);
        })->values()->all();

        return collect(static::regroupByPrinsip($sorted, fn($it) => $it->prinsip_dasar));
    }

    /**
     * Kelompokkan baris dengan Prinsip Dasar sama agar berurutan berdampingan (grup muncul sesuai
     * kemunculan pertama). Urutan ini dipakai bersama oleh Modal, Matriks, dan Print sehingga
     * nomor urut selalu identik di ketiganya.
     */
    public static function regroupByPrinsip(array $list, callable $keyFn): array
    {
        $buckets = [];
        foreach ($list as $row) {
            $buckets[(string) $keyFn($row)][] = $row;
        }

        return array_merge(...array_values($buckets ?: [[]]));
    }

    /**
     * Ambil seluruh item untuk Modal Kelola Master Item Audit (mode Default atau mode Bagian).
     */
    public static function getModalItemsForBagian(?string $bagian): array
    {
        $cleanBagian = trim((string) $bagian);

        $defaultItems = static::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('bagian')->orWhere('bagian', '');
            })
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        if ($cleanBagian === '') {
            $rows = [];
            foreach ($defaultItems as $def) {
                $rows[] = [
                    'id'                 => $def->id,
                    'bagian'             => '',
                    'is_default'         => true,
                    'is_custom_override' => false,
                    'is_enabled'         => true,
                    'prinsip_dasar'      => $def->prinsip_dasar,
                    'item_check'         => $def->item_check,
                    'standard'           => $def->standard,
                    'order_no'           => (int) $def->order_no,
                ];
            }

            $rows = static::regroupByPrinsip($rows, fn($r) => $r['prinsip_dasar']);

            return [
                'items'        => $rows,
                'active_count' => count($rows),
                'total_count'  => count($rows),
                'custom_count' => 0,
                'off_count'    => 0,
            ];
        }

        $settingsByItemId = OperatorComplianceItemBagianSetting::where('bagian', $cleanBagian)
            ->get()
            ->keyBy('item_id');

        $customBagianItems = static::where('is_active', true)
            ->where('bagian', $cleanBagian)
            ->orderBy('order_no')
            ->orderBy('id')
            ->get();

        $rows = [];

        foreach ($defaultItems as $def) {
            $st = $settingsByItemId->get($def->id);
            $hasOverride = $st && (
                $st->custom_prinsip_dasar !== null ||
                $st->custom_item_check !== null ||
                $st->custom_standard !== null ||
                $st->custom_order_no !== null
            );
            $isEnabled = $st ? (bool) $st->is_enabled : true;

            $rows[] = [
                'id'                 => $def->id,
                'bagian'             => '',
                'is_default'         => true,
                'is_custom_override' => (bool) $hasOverride,
                'is_enabled'         => $isEnabled,
                'prinsip_dasar'      => ($st && $st->custom_prinsip_dasar !== null && $st->custom_prinsip_dasar !== '') ? $st->custom_prinsip_dasar : $def->prinsip_dasar,
                'item_check'         => ($st && $st->custom_item_check !== null && $st->custom_item_check !== '') ? $st->custom_item_check : $def->item_check,
                'standard'           => ($st && $st->custom_standard !== null && $st->custom_standard !== '') ? $st->custom_standard : $def->standard,
                'order_no'           => ($st && $st->custom_order_no !== null) ? (int) $st->custom_order_no : (int) $def->order_no,
            ];
        }

        foreach ($customBagianItems as $cust) {
            $st = $settingsByItemId->get($cust->id);
            $isEnabled = $st ? (bool) $st->is_enabled : true;

            $rows[] = [
                'id'                 => $cust->id,
                'bagian'             => $cust->bagian,
                'is_default'         => false,
                'is_custom_override' => false,
                'is_enabled'         => $isEnabled,
                'prinsip_dasar'      => $cust->prinsip_dasar,
                'item_check'         => $cust->item_check,
                'standard'           => $cust->standard,
                'order_no'           => (int) $cust->order_no,
            ];
        }

        usort($rows, function ($a, $b) {
            return ((int) $a['order_no'] <=> (int) $b['order_no'])
                ?: ((int) $a['id'] <=> (int) $b['id']);
        });
        $rows = static::regroupByPrinsip($rows, fn($r) => $r['prinsip_dasar']);

        $activeCount = 0;
        $customCount = 0;
        $offCount = 0;
        foreach ($rows as $r) {
            if ($r['is_enabled']) {
                $activeCount++;
            } else {
                $offCount++;
            }
            if (!$r['is_default']) {
                $customCount++;
            }
        }

        return [
            'items'        => $rows,
            'active_count' => $activeCount,
            'total_count'  => count($rows),
            'custom_count' => $customCount,
            'off_count'    => $offCount,
        ];
    }

    /**
     * Bangun matriks entri harian [item_id][day] => status (1 flat query, bebas N+1).
     */
    public static function buildEntriesMatrix(int $checksheetId, $items): array
    {
        $entries = OperatorComplianceEntry::where('checksheet_id', $checksheetId)
            ->get(['item_id', 'day', 'status']);

        $matrix = [];
        foreach ($entries as $entry) {
            $matrix[$entry->item_id][$entry->day] = $entry->status;
        }

        return $matrix;
    }

    /**
     * Susun ulang order_no secara berurutan (1, 2, 3, ...) untuk bagian tertentu (atau Default jika null/kosong)
     */
    public static function resequenceOrderNumbers(?string $bagian): void
    {
        $cleanBagian = trim((string) $bagian);
        $query = static::where('is_active', true);

        if ($cleanBagian !== '') {
            // Hanya rapikan item "ekor" (setelah urutan Default). Item yang sengaja
            // disisipkan di posisi tertentu (order_no <= max Default) tidak digeser.
            $maxDefaultOrder = (int) (static::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('bagian')->orWhere('bagian', '');
                })
                ->max('order_no') ?? 0);
            $query->where('bagian', $cleanBagian)->where('order_no', '>', $maxDefaultOrder);
            $seq = $maxDefaultOrder + 1;
        } else {
            $query->where(function ($q) {
                $q->whereNull('bagian')->orWhere('bagian', '');
            });
            $seq = 1;
        }

        $items = $query->orderBy('order_no')->orderBy('id')->get();
        foreach ($items as $it) {
            if ((int) $it->order_no !== $seq) {
                $it->update(['order_no' => $seq]);
            }
            $seq++;
        }
    }

    // Relasi ke pengaturan per bagian (toggle ON/OFF & kustomisasi)
    public function bagianSettings()
    {
        return $this->hasMany(OperatorComplianceItemBagianSetting::class, 'item_id');
    }

    // Relasi ke entri-entri matriks harian
    public function entries()
    {
        return $this->hasMany(OperatorComplianceEntry::class, 'item_id');
    }
}
