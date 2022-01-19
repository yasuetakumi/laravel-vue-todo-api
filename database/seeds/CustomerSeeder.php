<?php
// -----------------------------------------------------------------------------
use Illuminate\Database\Seeder;
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
use App\Models\Customer;
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
class CustomerSeeder extends Seeder {
    // -------------------------------------------------------------------------
    public function run() {
        // ---------------------------------------------------------------------
        $customers = [
            [
                'name'      => 'Amazon', 
                'email'     => 'email@amazon.com',
                'phone'     => '813-4540-5951',
                'website'   => 'amazon.com',
            ],
            [
                'name'      => 'Google',
                'email'     => 'email@google.com',
                'phone'     => '812-3456-7891',
                'website'   => 'google.com',
            ],
            [
                'name'      => 'Facebook',
                'email'     => 'email@facebook.com',
                'phone'     => '812-3456-7891',
                'website'   => 'facebook.com',
            ],
            [
                'name'      => 'Apple',
                'email'     => 'email@apple.com',
                'phone'     => '812-3456-7891',
                'website'   => 'apple.com',
            ],
            [
                'name'      => 'Netflix',
                'email'     => 'email@netflix.com',
                'phone'     => '812-3456-7891',
                'website'   => 'netflix.com',
            ],
            [
                'name'      => 'LinkedIn',
                'email'     => 'email@linkedin.com',
                'phone'     => '812-3456-7891',
                'website'   => 'linkedin.com',
            ],
            [
                'name'      => 'Docomo',
                'email'     => 'email@nttdocomo.com',
                'phone'     => '812-3456-7891',
                'website'   => 'nttdocomo.co.jp',
            ],
            [
                'name'      => 'Microsoft',
                'email'     => 'email@microsoft.com',
                'phone'     => '812-3456-7891',
                'website'   => 'microsoft.com',
            ],
        ];
        // ---------------------------------------------------------------------
        foreach ($customers as $key => $customer) {
            Customer::create($customer);
        }
        // ---------------------------------------------------------------------
    }
    // -------------------------------------------------------------------------
}
// -----------------------------------------------------------------------------
