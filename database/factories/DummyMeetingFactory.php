<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\DummyMeeting;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Faker\Factory as FakerFactory;
use App\Models\User;

$factory->define(DummyMeeting::class, function (Faker $faker) {
    $faker  = FakerFactory::create('ja_JP');
    // postcode just for test
    $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
    $arr_user = User::pluck('id');

    return [
        'title' => $faker->realText(100),
        'customer' => rand(1, count(DummyMeeting::CUSTOMER)),
        'meeting_date' => Carbon::today()->addDays(rand(0, 365)),
        'location' => rand(0, 1),
        'registrant' => collect($arr_user, 1)->random(),
        'postcode' => collect($arr_postcode, 1)->random(),
        'address' => $faker->address,
        'phone' => $faker->phoneNumber,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
});
