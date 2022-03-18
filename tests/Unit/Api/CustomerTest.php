<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use UserRoleSeeder;
use CustomerSeeder;
use App\Models\User;
use App\Models\Customer;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * to run phpunit test :
     * vendor/bin/phpunit
     * 
     * with spesific group :
     * vendor/bin/phpunit --group customerTest
    */

    /** 
     * get user for login
    */
    public function testUserLogin()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 1)->create();
        return $user_login = User::find(1);
    }

    /**
     * @group customerTest
     * @group customerTestList
     * 
     * test get customer list 
     * and check the structure of the response
     */
    public function testCustomerList()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers?itemsPerPage=10&page=1&sortBy=&sortDesc=')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                        'customers' => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'email',
                                    'phone',
                                    'website',
                                    'created_at',
                                    'updated_at',
                                ]
                            ],
                            'first_page_url',
                            'from',
                            'last_page',
                            'last_page_url',
                            'next_page_url',
                            'path',
                            'per_page',
                            'prev_page_url',
                            'to',
                            'total',
                        ]
                    ],
                    'message'
                ]
            );
    }

    /**
     * @group customerTest
     * @group customerTestList
     * 
     * test get customers list 
     * filter by customers->name
     * and check the structure of the response
     */
    public function testCustomerListFilterName()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_check = Customer::find(1); // for filter
        $customer_check_not_found = Customer::find(2); // for test it not found
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers?itemsPerPage=10&page=1&sortBy=&sortDesc=&email=&phone=&website=&name='.$customer_check->name)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'name' => $customer_check->name
                ]
            )
            ->assertJsonMissing(
                [
                    'name' => $customer_check_not_found->name
                ]
            );
    }

    /**
     * @group customerTest
     * @group customerTestList
     * 
     * test get customers list 
     * filter by customers->email
     * and check the structure of the response
     */
    public function testCustomerListFilterEmail()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_check = Customer::find(1); // for filter
        $customer_check_not_found = Customer::find(2); // for test it not found
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers?itemsPerPage=10&page=1&sortBy=&sortDesc=&phone=&website=&name=&email='.$customer_check->email)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'email' => $customer_check->email
                ]
            )
            ->assertJsonMissing(
                [
                    'email' => $customer_check_not_found->email
                ]
            );
    }

    /**
     * @group customerTest
     * @group customerTestList
     * 
     * test get customers list 
     * filter by customers->phone
     * and check the structure of the response
     */
    public function testCustomerListFilterPhone()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_check = Customer::find(1); // for filter
        $customer_check_not_found = Customer::find(2); // for test it not found
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers?itemsPerPage=10&page=1&sortBy=&sortDesc=&email=&website=&name=&phone='.$customer_check->phone)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'phone' => $customer_check->phone
                ]
            )
            ->assertJsonMissing(
                [
                    'phone' => $customer_check_not_found->phone
                ]
            );
    }

    /**
     * @group customerTest
     * @group customerTestList
     * 
     * test get customers list 
     * filter by customers->website
     * and check the structure of the response
     */
    public function testCustomerListFilterWebsite()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_check = Customer::find(1); // for filter
        $customer_check_not_found = Customer::find(2); // for test it not found
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers?itemsPerPage=10&page=1&sortBy=&sortDesc=&email=&phone=&name=&website='.$customer_check->website)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'website' => $customer_check->website
                ]
            )
            ->assertJsonMissing(
                [
                    'website' => $customer_check_not_found->website
                ]
            );
    }

    /**
     * @group customerTest
     * 
     * test store customer
     * check the response
     * and check database
     */
    public function testCustomerIsCreatedSuccessfully() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'name'      => 'Customer'.$now, 
            'email'     => 'custome'.$now.'@mail.com',
            'phone'     => '1111-2222-3333',
            'website'   => 'cusomer'.$now.'.com',
        ];

        $this->actingAs($user_login, 'sanctum')
            ->postJson('api/customers', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
        );
        $this->assertDatabaseHas('customers', $payload);
    }

    /**
     * @group customerTest
     * 
     * test show customer in the correct state
     */
    public function testCustomerIsShownCorrectly()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_check = Customer::find(1);
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/customers/'.$customer_check->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => [
                        'id' => $customer_check->id,
                        'name' => $customer_check->name,
                        'email' => $customer_check->email,
                        'phone' => $customer_check->phone,
                        'website' => $customer_check->website,
                        'created_at' => is_null($customer_check->created_at) ? $customer_check->created_at : $customer_check->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => is_null($customer_check->created_at) ? $customer_check->created_at : $customer_check->created_at->format('Y-m-d H:i:s')
                    ],
                    'message' => 'Successfully process the request'
                ]
            );
    }

    /**
     * @group customerTest
     * 
     * test update customer
     * check the response
     * and check database
     */
    public function testUserIsUpdatedSuccessfully() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'name'      => 'Customer'.$now, 
            'email'     => 'custome'.$now.'@mail.com',
            'phone'     => '1111-2222-3333',
            'website'   => 'cusomer'.$now.'.com',
        ];
        $customer_update = Customer::find(1);
            
        $this->actingAs($user_login, 'sanctum')
            ->postJson('api/customers/'.$customer_update->id, $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        
        $payload['id'] = $customer_update->id;
        $this->assertDatabaseHas('customers', $payload);
    }

    /**
     * @group customerTest
     * 
     * test delete customer 
     * check the response
     * and check database
     */
    public function testCustomerIsDestroyedSuccessfully() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer

        $customer_to_delete = Customer::find(1);
        $this->actingAs($user_login, 'sanctum')
            ->deleteJson('api/customers/'.$customer_to_delete->id)
            ->assertExactJson(
                [
                    'data' => 1, // 1 mean success delete data
                    'message' => 'Successfully process the request'
                ]
            );

        $customer_check = [
            'name'      => $customer_to_delete->name,
            'email'     => $customer_to_delete->email,
            'phone'     => $customer_to_delete->phone,
            'website'   => $customer_to_delete->website,
        ];
        $this->assertDatabaseMissing('customers', $customer_check);
    }
}
