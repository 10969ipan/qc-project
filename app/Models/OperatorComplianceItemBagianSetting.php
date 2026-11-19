<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan toggle ON/OFF dan kustomisasi teks per bagian untuk Master Item Audit Kepatuhan Operator.
 */
class OperatorComplianceItemBagianSetting extends Model
{
    use HasFactory;

    protected $table = 'operator_compliance_item_bagian_settings';

    protected $fillable = [
        'item_id',
        'bagian',
        'is_enabled',
        'custom_prinsip_dasar',
        'custom_item_check',
        'custom_standard',
        'custom_order_no',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'custom_order_no' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(OperatorComplianceItem::class, 'item_id');
    }
}
