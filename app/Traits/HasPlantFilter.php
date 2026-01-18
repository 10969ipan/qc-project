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
            if (Auth::check() && Auth::user()->plant_id) {
                $role = Auth::user()->role;
                $userPlantId = Auth::user()->plant_id;

                $exemptRoles = ['admin', 'manager', 'asst_manager', 'supervisor', 'kashift', 'karu_qc'];

                if (!in_array($role, $exemptRoles)) {
                    $query->where($query->getModel()->getTable() . '.plant_id', $userPlantId);
                }
            }
        });
    }
}
