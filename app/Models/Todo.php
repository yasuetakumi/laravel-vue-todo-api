<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use SoftDeletes;

    protected $table = 'todos';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'startDate',
        'deadline',
        'priority',
        'status'
    ];
}
