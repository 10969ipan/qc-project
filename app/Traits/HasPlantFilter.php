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

                $exemptRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc'];

                if (!in_array($role, $exemptRoles)) {
                    $query->where($query->getModel()->getTable() . '.plant', $userPlant);
                }
            }
        });
    }
}
