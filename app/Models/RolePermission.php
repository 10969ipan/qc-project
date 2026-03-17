<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'menu_id',
        'can_view',
        'can_input',
        'can_edit',
        'can_delete',
        'can_approve',
        'can_export'
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_input' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_approve' => 'boolean',
        'can_export' => 'boolean',
    ];

    public function menu()
    {
        return $this->belongsTo(AppMenu::class, 'menu_id');
    }
}
