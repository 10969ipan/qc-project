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
}
