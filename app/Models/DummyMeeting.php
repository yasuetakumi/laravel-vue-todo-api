<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DummyMeeting extends Model {
    const CUSTOMER = [

        ['value' => 1, 'text' => 'Amazon'],
        ['value' => 2, 'text' => 'Google'],
        ['value' => 3, 'text' => 'Facebook'],
        ['value' => 4, 'text' => 'Apple'],
        ['value' => 5, 'text' => 'Netflix'],
        ['value' => 6, 'text' => 'LinkedIn'],
        ['value' => 7, 'text' => 'Docomo'],
        ['value' => 8, 'text' => 'Microsoft'],
    ];

    const ATTENDEE = [
        ['value' => 0, 'text' => 'President'],
        ['value' => 1, 'text' => 'Vice President'],
    ];

    protected $fillable = [
        'title',
        'customer',
        'attendee',
        'meeting_date',
        'location_image_url'
    ];
}