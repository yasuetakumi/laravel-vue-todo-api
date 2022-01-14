<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// for Mobile Sanctum
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'display_name', 'email', 'password', 'user_role_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'user_role_name'
    ];

    public function user_role()
    {
        return $this->belongsTo('App\Models\UserRole', 'user_role_id');
    }

    public function getUserRoleNameAttribute()
    {
        return $this->user_role->label;
    }
}
