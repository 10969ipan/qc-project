<?php

namespace App\Helpers;

use Carbon\Carbon;

class ShiftHelper
{
    /**
     * Get production date based on datetime.
     * Standard day change is at 07:00 AM.
     */
    public static function getProductionDate($dateTime = null)
    {
        $dt = $dateTime ? Carbon::parse($dateTime) : now();
        $hour = (int) $dt->hour;

        // Common rule: Before 7 AM belongs to previous day
        if ($hour < 7) {
            return $dt->copy()->subDay()->format('Y-m-d');
        }

        return $dt->format('Y-m-d');
    }

    /**
     * Check if a date is a half-day (Saturday).
     */
    public static function isHalfDay($date)
    {
        return Carbon::parse($date)->dayOfWeek === Carbon::SATURDAY;
    }

    /**
     * Get shift number based on datetime.
     */
    public static function getShift($dateTime = null)
    {
        $dt = $dateTime ? Carbon::parse($dateTime) : now();
        $hour = (int) $dt->hour;

        if (self::isHalfDay($dt)) {
            // Saturday Shifts:
            // S1: 07:00 - 12:00
            // S2: 12:00 - 17:00
            // S3: 17:00 - 23:00
            if ($hour >= 7 && $hour < 12)
                return 1;
            if ($hour >= 12 && $hour < 17)
                return 2;
            if ($hour >= 17 && $hour < 23)
                return 3;

            // Before 7 AM on Saturday is technically still Friday Shift 3
            if ($hour < 7)
                return 3;

            return null; // Production ends at 23:00 Sat
        }

        // Normal Day Shifts (Mon-Fri and Sun if production):
        // S1: 07:00 - 15:00
        // S2: 15:00 - 23:00
        // S3: 23:00 - 07:00 (Next Day)
        if ($hour >= 7 && $hour < 15)
            return 1;
        if ($hour >= 15 && $hour < 23)
            return 2;

        // S3 is 23:00 to 07:00
        return 3;
    }

    /**
     * Get shift start time for monitoring purposes.
     */
    public static function getShiftStartTime($productionDate, $shift, $dayOfWeek)
    {
        $date = Carbon::parse($productionDate)->format('Y-m-d');

        if ($dayOfWeek === Carbon::SATURDAY) {
            switch ($shift) {
                case 1:
                    return Carbon::parse($date . ' 07:00:00');
                case 2:
                    return Carbon::parse($date . ' 12:00:00');
                case 3:
                    return Carbon::parse($date . ' 17:00:00');
            }
        }

        switch ($shift) {
            case 1:
                return Carbon::parse($date . ' 07:00:00');
            case 2:
                return Carbon::parse($date . ' 15:00:00');
            case 3:
                return Carbon::parse($date . ' 23:00:00');
        }

        return null;
    }
}
