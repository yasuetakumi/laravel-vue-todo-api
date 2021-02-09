<?php

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $defaultAdmin = [
            'id'                => '1',
            'display_name'      => 'Administrator',
            'email'             => 'admin@system.com',
            'password'          => Hash::make('12345678'),
            'created_at'        => Carbon::now(),
            'updated_at'        => Carbon::now(),
        ];

        Admin::insert($defaultAdmin);
    }
}
