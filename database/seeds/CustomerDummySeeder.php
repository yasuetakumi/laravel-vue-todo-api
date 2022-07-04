<?php

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = Customer::all();

        $alpha = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach($customers as $index => $customer) {
            $customer->update([
                'name'      => 'Company'.$alpha[$index],
                'email'     => 'email@company'.$alpha[$index].'.com',
                'website'   => 'company'.$alpha[$index].'.com',
            ]);
        }
    }
}
