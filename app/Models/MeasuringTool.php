<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MeasuringTool extends Model
{
    use HasFactory, \App\Traits\HasUuid;

    protected $table = 'measuring_tools';

    protected $fillable = [
        'plant_id',
        'name',
        'serial_number',
        'category',
        'code',
        'method',
        'description',
        'variables',
        'order_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
        'order_no' => 'integer',
    ];

    /**
     * Check if this measuring tool uses OK/NG judgment mode
     */
    public function isOkNg(): bool
    {
        return strtolower((string)$this->category) === 'ok_ng';
    }

    /**
     * Human-readable category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->isOkNg() ? 'OK / NG' : 'Numeric (Angka)';
    }

    /**
     * In-memory cache for O(1) dictionary lookups during checksheet / item list rendering
     */
    protected static array $memoryCache = [];

    /**
     * Get the plant that owns the measuring tool.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    /**
     * Display name formatted as "Name (Serial Number)" if serial number is present,
     * otherwise just "Name".
     */
    public function getDisplayNameAttribute(): string
    {
        $name = trim((string) $this->name);
        $serial = trim((string) $this->serial_number);

        if ($serial !== '') {
            return "{$name} ({$serial})";
        }

        return $name;
    }

    /**
     * Scope to active tools ordered by order_no and name
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order_no')->orderBy('name');
    }

    /**
     * Scope by plant
     */
    public function scopeForPlant($query, $plantId)
    {
        $resolved = Plant::resolveId($plantId);
        return $query->where('plant_id', $resolved);
    }

    /**
     * Get active measuring tools for a plant (cached)
     *
     * @param mixed $plantIdentifier
     * @return \Illuminate\Support\Collection
     */
    public static function getForPlant($plantIdentifier)
    {
        $plantId = Plant::resolveId($plantIdentifier);
        if (!$plantId) {
            return collect();
        }

        if (isset(self::$memoryCache[$plantId])) {
            return self::$memoryCache[$plantId];
        }

        $tools = Cache::remember("measuring_tools_list_{$plantId}", 43200, function () use ($plantId) {
            return self::where('plant_id', $plantId)
                ->where('is_active', true)
                ->orderBy('order_no')
                ->orderBy('name')
                ->get();
        });

        return self::$memoryCache[$plantId] = $tools;
    }

    /**
     * Resolve a raw tool name (e.g., 'CALIPER') to the plant's formatted display name
     * (e.g., 'Digimatic Caliper (54A05051)') in O(1) time without extra queries.
     *
     * @param string|null $rawToolName
     * @param mixed $plantIdentifier
     * @return string
     */
    public static function resolveDisplayName(?string $rawToolName, $plantIdentifier): string
    {
        if ($rawToolName === null || trim($rawToolName) === '') {
            return '';
        }

        $rawTrim = trim($rawToolName);
        $plantId = Plant::resolveId($plantIdentifier);
        if (!$plantId) {
            return $rawTrim;
        }

        $tools = self::getForPlant($plantId);
        if ($tools->isEmpty()) {
            return $rawTrim;
        }

        $rawUpper = strtoupper($rawTrim);

        // 1. Exact match on name
        $matched = $tools->first(function ($t) use ($rawUpper) {
            return strtoupper(trim((string)$t->name)) === $rawUpper;
        });

        // 2. Exact match on code (if tool was referenced by code)
        if (!$matched) {
            $matched = $tools->first(function ($t) use ($rawUpper) {
                return !empty($t->code) && strtoupper(trim((string)$t->code)) === $rawUpper;
            });
        }

        // 3. Loose match: if raw name matches display_name or starts with tool name
        if (!$matched) {
            $matched = $tools->first(function ($t) use ($rawUpper) {
                $toolUpper = strtoupper(trim((string)$t->name));
                return str_contains($rawUpper, $toolUpper) || str_contains($toolUpper, $rawUpper);
            });
        }

        if ($matched) {
            return $matched->display_name;
        }

        return $rawTrim;
    }

    /**
     * Resolve the category ('numeric' vs 'ok_ng') of a tool name for a given plant
     *
     * @param string|null $rawToolName
     * @param mixed $plantIdentifier
     * @return string 'numeric'|'ok_ng'
     */
    public static function resolveCategory(?string $rawToolName, $plantIdentifier): string
    {
        if ($rawToolName === null || trim($rawToolName) === '') {
            return 'numeric';
        }

        $plantId = Plant::resolveId($plantIdentifier);
        if (!$plantId) {
            return 'numeric';
        }

        $tools = self::getForPlant($plantId);
        if ($tools->isEmpty()) {
            return 'numeric';
        }

        $rawUpper = strtoupper(trim($rawToolName));

        $matched = $tools->first(function ($t) use ($rawUpper) {
            $toolUpper = strtoupper(trim((string)$t->name));
            return $toolUpper === $rawUpper || str_contains($rawUpper, $toolUpper) || str_contains($toolUpper, $rawUpper);
        });

        return $matched ? ($matched->category ?? 'numeric') : 'numeric';
    }

    /**
     * Clear cache on change
     */
    public static function clearCacheForPlant($plantId = null)
    {
        self::$memoryCache = [];
        if ($plantId) {
            Cache::forget("measuring_tools_list_{$plantId}");
        } else {
            $allPlants = Plant::pluck('id');
            foreach ($allPlants as $pId) {
                Cache::forget("measuring_tools_list_{$pId}");
            }
        }
        Cache::forget('fpa_standards');
        Cache::forget('in_proc_standards');
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            self::clearCacheForPlant($model->plant_id);
        });

        static::deleted(function ($model) {
            self::clearCacheForPlant($model->plant_id);
        });
    }
}
