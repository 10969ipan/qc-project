<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPlantFilter
{
    /**
     * Boot the trait and add the global scope.
     */
    protected static function bootHasPlantFilter()
    {
        static::addGlobalScope('plant', function ($query) {
            if (Auth::check() && Auth::user()->plant) {
                $role = Auth::user()->role;
                $userPlant = Auth::user()->plant;

                if ($role !== 'admin') {
                    // SPV Plant Jakarta sees ALL data
                    $isSpvJakarta = ($role === 'supervisor' || $role === 'supervisor_plating') && $userPlant === 'jakarta';

                    if (!$isSpvJakarta) {
                        $query->where($query->getModel()->getTable() . '.plant', $userPlant);
                    }
                }
            }
        });
    }
}
