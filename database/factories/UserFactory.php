<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Faker\Factory as FakerFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    $faker  = FakerFactory::create('ja_JP');
    return [
        'user_role_id'      => rand(1, 2), // UserRole:ADMIN or UserRole::USER
        'display_name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => Hash::make('12345678'), // password
        'remember_token' => Str::random(10),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
});
