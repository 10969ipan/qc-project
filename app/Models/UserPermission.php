<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_id',
        'can_view',
        'can_input',
        'can_edit',
        'can_delete',
        'can_approve',
        'can_approve_all',
        'can_export',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_input' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_approve' => 'boolean',
        'can_approve_all' => 'boolean',
        'can_export' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menu()
    {
        return $this->belongsTo(AppMenu::class, 'menu_id');
    }
}
