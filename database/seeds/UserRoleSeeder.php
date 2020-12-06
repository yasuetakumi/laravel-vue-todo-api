<?php

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'id'         => UserRole::ADMIN,
                'name'       => 'admin',
                'label'      => 'Administrator',
            ],
            [
                'id'         => UserRole::USER,
                'name'       => 'user',
                'label'      => 'User',
            ]
        ];
        UserRole::insert($roles);
    }
}
