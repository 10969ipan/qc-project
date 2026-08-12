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
            $setting = self::where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->orderBy('id', 'desc')
                ->first();

            if (!$setting) {
                $setting = self::where('key', $key)->first();
            }

            if (!$setting) return $default;

            $value = $setting->value;
            
            // Try to decode JSON if it looks like JSON
            if (is_string($value)) {
                $trimmed = trim($value);
                if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                    $decoded = json_decode($trimmed, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                }
            }

            return $value;
        } catch (\Exception $e) {
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
            'AWAL PRODUKSI',
            'OPERATOR ISTIRAHAT',
            'MATI LISTRIK',
            'PERBAIKAN NG',
            'UPDATE KETRIK',
            'GANTI MATERIAL',
            'PERBAIKAN MOLD',
            'PERBAIKAN MESIN',
        ];

        $settingVal = self::getValue('fpa_categories');
        if ($settingVal !== null && $settingVal !== '') {
            $raw = [];
            if (is_array($settingVal)) {
                $raw = $settingVal;
            } else {
                $str = trim((string) $settingVal);
                if ((str_starts_with($str, '"') && str_ends_with($str, '"')) || (str_starts_with($str, "'") && str_ends_with($str, "'"))) {
                    $str = trim(substr($str, 1, -1));
                    $str = stripslashes($str);
                }
                $decoded = json_decode($str, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $raw = $decoded;
                } else {
                    $raw = preg_split('/\r\n|\r|\n|,/', $str);
                }
            }

            $clean = [];
            foreach ($raw as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $val = strtoupper(trim((string) $item));
                    $val = trim($val, "[]\"'");
                    if ($val !== '') {
                        $clean[] = $val;
                    }
                }
            }

            if (!empty($clean)) {
                return array_values(array_unique($clean));
            }
        }

        return $defaults;
    }
}
