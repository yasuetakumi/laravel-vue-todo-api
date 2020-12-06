<?php

use App\Models\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultAdmin = [
            'user_role_id'      => UserRole::ADMIN,
            'display_name'      => 'Administrator',
            'email'             => 'admin@company.com',
            'password'          => Hash::make('12345678'),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        $defaultUser = [
            'user_role_id'      => UserRole::USER,
            'display_name'      => 'User',
            'email'             => 'user@company.com',
            'password'          => Hash::make('12345678'),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        User::insert([$defaultAdmin, $defaultUser]);
    }
}
