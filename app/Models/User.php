<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plant_id',
        'initials',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the plant that the user belongs to.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    // Accessor untuk inisial nama
    // Menggunakan value dari DB jika ada, jika tidak generate dari nama
    public function getInitialsAttribute($value)
    {
        if ($value) {
            return $value; // Return exactly what is in DB per user request
        }

        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= substr($word, 0, 1);
        }
        return $initials;
    }

    // Helper untuk cek role
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user has permission for a specific menu and action
     */
    public function hasPermission($menuId, $action = 'view')
    {
        // 1. Check User Specific Override
        $userPerm = \App\Models\UserPermission::where('user_id', $this->id)
            ->where('menu_id', $menuId)
            ->first();
        
        if ($userPerm) {
            $field = "can_{$action}";
            return (bool) ($userPerm->$field ?? false);
        }

        // 2. Fallback to Role Permission
        $rolePerm = \App\Models\RolePermission::where('role', $this->role)
            ->where('menu_id', $menuId)
            ->first();
        
        if ($rolePerm) {
            $field = "can_{$action}";
            return (bool) ($rolePerm->$field ?? false);
        }

        return false;
    }
}