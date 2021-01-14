<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\DummyMeeting;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(DummyMeeting::class, function (Faker $faker) {
    return [
        'title' => $faker->realText(100),
        'customer' => rand(0, count(DummyMeeting::CUSTOMER) - 1),
        'meeting_date' => Carbon::today()->addDays(rand(0, 365)),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
});
