<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\DummyMeeting;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(DummyMeeting::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(100),
        'customer' => rand(1, count(DummyMeeting::CUSTOMER)),
        'meeting_date' => Carbon::today()->addDays(rand(0, 365)),
        'location' => rand(0, 1),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
});
