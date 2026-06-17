<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThicknessStandard extends Model
{
    use \App\Traits\HasUuid, \App\Traits\HasPlantFilter;

    protected $fillable = [
        'plant_id',
        'part_name',
        'customer',
        'standard_code',
        'standard_name',
        'thickness_cu_std',
        'thickness_ni_std',
        'thickness_cr_std',
        'corrodkote',
        'cass_test',
        'salt_spray_test',
        'porecount_test',
        'cross_cut_test',
    ];
}
