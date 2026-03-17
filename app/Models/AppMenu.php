<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'route',
        'parent_id',
        'order',
        'is_active',
        'is_maintenance',
        'maintenance_message',
        'plant_code'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_maintenance' => 'boolean',
    ];

    public function children()
    {
        return $this->hasMany(AppMenu::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(AppMenu::class, 'parent_id');
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'menu_id');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'menu_id');
    }
}
