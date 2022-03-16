<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use UserRoleSeeder;
use CustomerSeeder;
use DummyMeetingSeeder;
use App\Models\User;
use App\Models\DummyMeeting;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * to run phpunit test :
     * vendor/bin/phpunit
     * 
     * with spesific group :
     * vendor/bin/phpunit --group meetingTest
    */

    /** 
     * get user for login
    */
    public function testUserLogin($number_user = 1)
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, $number_user)->create();
        return $user_login = User::find(1);
    }

    /** 
     * run seeder customer and meeting
    */
    public function runSeederCustomerAndMeeting()
    {
        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer
        // --- run seeder meeting
        $this->seed(DummyMeetingSeeder::class);
        // --- END run seeder meeting
    }

    /**
     * @group meetingTest
     * @group meetingTestList
     * 
     * test get meeting list 
     * and check the structure of the response
     */
    public function testMeetingList()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&title=&customer=&location=&meeting_date_start=&meeting_date_end=&registrant=')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                        'meetings' => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'customer' => [
                                        'id',
                                        'name',
                                        'email',
                                        'phone',
                                        'website',
                                        'created_at',
                                        'updated_at',
                                    ],
                                    'meeting_date',
                                    'location',
                                    'postcode',
                                    'address',
                                    'phone',
                                    'registrant',
                                    'location_image_url',
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
                        ],
                        'formData' => [
                            'customers' => [
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
                            'locations' => [
                                '*' => [
                                    'value',
                                    'text'
                                ]
                            ],
                            'registrants' => [
                                '*' => [
                                    'id',
                                    'display_name',
                                    'user_role_id',
                                    'user_role_name',
                                    'user_role' => [
                                        'id',
                                        'name',
                                        'label',
                                        'created_at',
                                        'updated_at',
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'message'
                ]
            );
    }

    /**
     * @group meetingTest
     * @group meetingTestList
     * 
     * test get meeting list 
     * filter by meetings->title
     * and check the structure of the response
     */
    public function testMeetingListFilterTitle()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $meeting_check = DummyMeeting::find(1); // --- get data meetings for checking
        $meeting_check_not_found = DummyMeeting::find(2); // --- get data to check data does not exist
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&customer=&location=&meeting_date_start=&meeting_date_end=&registrant=&title='.$meeting_check->title)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'title' => $meeting_check->title,
                ]
            )
            ->assertJsonMissing(
                [
                    'title' => $meeting_check_not_found->title,
                ]
            );
    }

    /**
     * @group meetingTest
     * @group meetingTestList
     * 
     * test get meeting list 
     * filter by meetings->customer
     * and check the structure of the response
     */
    public function testMeetingListFilterCustomer()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // need confirm, because if use "with('customer')" data customer not display
        // i think its because column name and relationship name is same
        // for now just check meetings->title
        $meeting_check = DummyMeeting::find(1); // --- get data meetings for checking
        $meeting_check_not_found = DummyMeeting::where('customer', '!=' , $meeting_check->customer)->first(); // --- get data to check data does not exist
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&location=&meeting_date_start=&meeting_date_end=&registrant=&title=&customer='.$meeting_check->customer)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'title'    => $meeting_check->title,
                ]
            )
            ->assertJsonMissing(
                [
                    'title' => $meeting_check_not_found->title
                ]
            );
    }

    /**
     * @group meetingTest
     * @group meetingTestList
     * 
     * test get meeting list 
     * filter by meetings->meeting_date
     * and check the structure of the response
     */
    public function testMeetingListFilterMeetingDate()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // --- get data meetings for checking
        $meeting_check = DummyMeeting::find(1); 
        $meeting_date_check = new Carbon($meeting_check->meeting_date);
        // --- END get data meetings for checking
        // --- get data to check data does not exist
        $meeting_check_not_found = DummyMeeting::whereDate('meeting_date', '!=' , $meeting_date_check->format('Y-m-d'))->first();
        $meeting_date_check_not_found = new Carbon($meeting_check_not_found->meeting_date);
        // --- END get data to check data does not exist
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&location=&registrant=&title=&customer=&meeting_date_start='.$meeting_date_check->format('Y-m-d').'&meeting_date_end='.$meeting_date_check->format('Y-m-d'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'title' => $meeting_check->title,
                    'meeting_date' => $meeting_date_check->format('Y-m-d H:i:s'),
                ]
            )
            ->assertJsonMissing(
                [
                    'title' => $meeting_check_not_found->title,
                    'meeting_date' => $meeting_date_check_not_found->format('Y-m-d H:i:s'),
                ]
            );
    }

    /**
     * @group meetingTest
     * @group meetingTestList
     * 
     * test get meeting list 
     * filter by meetings->location
     * and check the structure of the response
     */
    public function testMeetingListFilterLocation()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // --- get data meetings for checking
        $meeting_check = DummyMeeting::where('location', 0)->first();
        // --- get data to check data does not exist
        $meeting_check_not_found = DummyMeeting::where('location', 1)->first(); 
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&registrant=&title=&customer=&meeting_date_start=&meeting_date_end=&location='.$meeting_check->location)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'title' => $meeting_check->title,
                    'location' => $meeting_check->location,
                ]
            )
            ->assertJsonMissing(
                [
                    'title' => $meeting_check_not_found->title,
                    'location' => $meeting_check_not_found->location,
                ]
            );
    }

    /**
     * @group meetingTest1
     * @group meetingTestList
     * 
     * test get meeting list 
     * filter by meetings->registrant 
     * and check the structure of the response
     */
    public function testMeetingListFilterRegistrant ()
    {
        // -- get user for login
        $user_login = $this->testUserLogin(20);
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // --- get data meetings for checking
        $meeting_check = DummyMeeting::join('users', 'users.id', '=', 'meetings.registrant')
            ->select('meetings.*', 'users.id as registrant_id', 'users.display_name as r_display_name', 'users.email as r_email')
            ->with('registrant')
            ->where('meetings.id', 1)
            ->first();
        // --- get data to check data does not exist
        $meeting_check_not_found = DummyMeeting::join('users', 'users.id', '=', 'meetings.registrant')
            ->select('meetings.*', 'users.id as registrant_id', 'users.display_name as r_display_name', 'users.email as r_email')
            ->with('registrant')
            ->where('registrant', '!=', $meeting_check->registrant_id)
            ->first(); 
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&title=&customer=&meeting_date_start=&meeting_date_end=&location=&registrant='.$meeting_check->r_display_name)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'title' => $meeting_check->title
                ]
            )
            ->assertJsonMissing(
                [
                    'title' => $meeting_check_not_found->title,
                ]
            );
    }
}
