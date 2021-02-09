<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model {
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'display_name',
        'password',
        'email'
    ];
}
