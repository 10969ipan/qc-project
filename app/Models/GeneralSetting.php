<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $table = 'general_settings';
    
    protected $fillable = [
        'key', 
        'plant_code', 
        'category', 
        'value', 
        'description'
    ];

    /**
     * Helper to get a setting value
     */
    public static function getValue($key, $default = null)
    {
        try {
            $setting = self::where('key', $key)->first();
            if (!$setting) return $default;

            $value = $setting->value;
            
            // Try to decode JSON if it looks like JSON
            if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return $value;
        } catch (\Exception $e) {
            // Log error if needed, but return default to prevent breaking the flow
            return $default;
        }
    }

    /**
     * Helper to get Document Header configuration
     */
    public static function getDocHeader($module, $plantCode, $defaults = [])
    {
        $setting = self::where('category', 'document_control')
            ->where('key', $module)
            ->where('plant_code', strtolower($plantCode))
            ->first();

        if ($setting) {
            $decoded = json_decode($setting->value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($defaults, $decoded);
            }
        }

        return $defaults;
    }

    /**
     * Helper to get First Piece Approval Categories
     */
    public static function getFpaCategories()
    {
        $defaults = [
            'awal produksi',
            'operator istirahat',
            'mati listrik',
            'perbaikan NG',
            'update ketrik',
            'ganti material',
            'perbaikan mold',
            'perbaikan mesin',
        ];

        $settingVal = self::getValue('fpa_categories');
        if ($settingVal) {
            if (is_array($settingVal)) {
                return array_values(array_filter(array_map('trim', $settingVal)));
            }
            $lines = explode("\n", str_replace("\r", "", (string) $settingVal));
            $items = array_values(array_filter(array_map('trim', $lines)));
            return !empty($items) ? $items : $defaults;
        }

        return $defaults;
    }
}
