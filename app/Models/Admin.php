<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model {
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'display_name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
