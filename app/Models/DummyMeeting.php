<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Customer;

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

    const LOCATION = [
        ['value' => 0, 'text' => 'Internal'],
        ['value' => 1, 'text' => 'External'],
    ];

    // change name table from dummy_meetings to meetings
    protected $table = 'meetings';

    protected $fillable = [
        'title',
        'customer',
        'location',
        'meeting_date',
        'registrant',
        'location_image_url',
        'postcode',
        'address',
        'phone'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer');
    }

    public function registrant() {
        return $this->belongsTo(User::class, 'registrant');
    }
}
