<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property string|null $file_path
 * @property string|null $customer
 * @property string|null $part_number
 * @property string|null $sap_code
 * @property array|null $dimension_standards
 * @property array|null $defects
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 */
class Item extends Model
{
    use HasFactory, \App\Traits\HasPlantFilter, \App\Traits\HasUuid;

    protected $table = 'items';

    protected $fillable = [
        'plant_id',
        'name',
        'category_id',
        'file_path',
        'file_paths',
        'similar_part_file_path',
        'customer',
        'part_number',
        'sap_code',
        'dimension_standards',
        'weight_standard',
        'defects',
        'cavity',
        'standard_cycle_time',
        'approval_status',
        'approved_by',
        'approved_at',
        'spv_approved_by',
        'spv_approved_at',
        'asst_mgr_approved_by',
        'asst_mgr_approved_at',
        'mgr_approved_by',
        'mgr_approved_at',
        'rejection_reason',
        'requested_by',
        'pending_changes',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_standards' => 'array',
        'file_paths' => 'array',
        'pending_changes' => 'array',
        'approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
        'asst_mgr_approved_at' => 'datetime',
        'mgr_approved_at' => 'datetime',
    ];

    /**
     * Get the category that owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the plant that owns the item.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Get user who requested this item.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get user who approved this item.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function spvApprover()
    {
        return $this->belongsTo(User::class, 'spv_approved_by');
    }

    public function asstMgrApprover()
    {
        return $this->belongsTo(User::class, 'asst_mgr_approved_by');
    }

    public function mgrApprover()
    {
        return $this->belongsTo(User::class, 'mgr_approved_by');
    }

    /**
     * Scope query to only approved items.
     */
    public function scopeApproved($query)
    {
        return $query->where($this->getTable() . '.approval_status', 'approved');
    }

    /**
     * Scope a query to filter items by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array|string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        // By default, checksheets and transactions querying items by category only receive approved items
        $query->where($this->getTable() . '.approval_status', 'approved');

        if (is_array($category)) {
            // If array of category names, get IDs first
            if (count($category) > 0 && is_string($category[0])) {
                $categoryIds = Category::withoutGlobalScope('plant')->whereIn('name', $category)->pluck('id');
                return $query->whereIn('category_id', $categoryIds);
            }
            // If array of IDs
            return $query->whereIn('category_id', $category);
        }

        // If category name (string)
        if (is_string($category) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $category)) {
            $categoryIds = Category::withoutGlobalScope('plant')->where('name', $category)->pluck('id');
            return $query->whereIn('category_id', $categoryIds);
        }

        // If category ID (UUID string)
        return $query->where('category_id', $category);
    }

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('fpa_standards');
            \Illuminate\Support\Facades\Cache::forget('in_proc_standards');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('fpa_standards');
            \Illuminate\Support\Facades\Cache::forget('in_proc_standards');
        });
    }
}
