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
        'customer',
        'part_number',
        'sap_code',
        'dimension_standards',
        'defects',
    ];

    protected $casts = [
        'defects' => 'array',
        'dimension_standards' => 'array',
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
     * Scope a query to filter items by category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string|array|string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
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
            $categoryId = Category::withoutGlobalScope('plant')->where('name', $category)->value('id');
            return $query->where('category_id', $categoryId);
        }

        // If category ID (UUID string)
        return $query->where('category_id', $category);
    }
}
