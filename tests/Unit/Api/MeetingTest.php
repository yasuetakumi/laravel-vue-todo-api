<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use UserRoleSeeder;
use CustomerSeeder;
use DummyMeetingSeeder;
use App\Models\User;
use App\Models\DummyMeeting;
use App\Models\Customer;

class MeetingTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * to run phpunit test :
     * vendor/bin/phpunit
     * 
     * with spesific group :
     * vendor/bin/phpunit --group meetingTest
     * 
     * note :
     * when you want to run tests related to image uploads,
     * it is required to have an image file in path
     * laravel6-spa-api-starter-kit\public\storage\uploads\dummy-img.jpg
     * this can be changed when using the function $this->prepareFileUpload()
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
     * @group meetingTest
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

    /**
     * @group meetingTest
     * 
     * test store meetings
     * check the response
     * and check database
     */
    public function testMeetingIsCreatedSuccessfullyWithoutImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin(10);
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $now = Carbon::now()->format('Y_m_d');
        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();

        $payload = [
            'title' => 'Meeting '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay()->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'address '.$selected_postcode,
            'phone' => '987654321',
            'location_image_modified' => 0 // 1 if there is image data or update image, 0 if no image data
        ];

        $response = $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );

        unset($payload['location_image_modified']);
        $this->assertDatabaseHas('meetings', $payload);
    }

    /**
     * @group meetingTest
     * 
     * test show meeting in the correct state
     */
    public function testMeetingIsShownCorrectlyWithoutImage()
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $meeting_check = DummyMeeting::find(1);
        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings/'.$meeting_check->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => [
                        'address' => $meeting_check->address,
                        'created_at' => $meeting_check->created_at->format('Y-m-d H:i:s'),
                        'customer' => $meeting_check->customer,
                        'id' => $meeting_check->id,
                        'location' => $meeting_check->location,
                        'location_image_url' => $meeting_check->location_image_url,
                        'meeting_date' => $meeting_check->meeting_date,
                        'phone' => $meeting_check->phone,
                        'postcode' => $meeting_check->postcode,
                        'registrant' => $meeting_check->registrant,
                        'title' => $meeting_check->title,
                        'updated_at' => $meeting_check->updated_at->format('Y-m-d H:i:s'),
                    ],
                    'message' => 'Successfully process the request'
                ]
            );
    }

    /**
     * @group meetingTest
     * 
     * test update meeting
     * check the response
     * and check database
     */
    public function testMeetingIsUpdatedSuccessfullyWithoutImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'title' => 'Meeting Update '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay(5)->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'address '.$selected_postcode,
            'phone' => '987654321',
            'location_image_modified' => 0 // 1 if there is image data or update image, 0 if no image data
        ];
        $meeting_update = DummyMeeting::find(1);
            
        $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings/'.$meeting_update->id, $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        
        unset($payload['location_image_modified']);
        $payload['id'] = $meeting_update->id;
        $this->assertDatabaseHas('meetings', $payload);
    }

    /**
     * @group meetingTest
     * 
     * test delete meeting 
     * check the response
     * and check database
     */
    public function testMeetingIsDestroyedSuccessfullyWithoutImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $meeting_to_delete = DummyMeeting::find(1);
        $this->actingAs($user_login, 'sanctum')
            ->deleteJson('api/meetings/'.$meeting_to_delete->id)
            ->assertExactJson(
                [
                    'data' => 1, // 1 mean success delete data
                    'message' => 'Successfully process the request'
                ]
            );

        $meeting_check = [
            'title' => $meeting_to_delete->title,
            'customer' => $meeting_to_delete->customer,
            'registrant' => $meeting_to_delete->registrant,
            'location' => $meeting_to_delete->location,
            'meeting_date' => $meeting_to_delete->meeting_date,
            'postcode' => $meeting_to_delete->postcode,
            'address' => $meeting_to_delete->address,
            'phone' => $meeting_to_delete->phone
        ];
        $this->assertDatabaseMissing('meetings', $meeting_check);
    }

    /** 
     * for help for test upload file
    */
    public function prepareFileUpload($path)
    {
        TestCase::assertFileExists($path);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        return new \Symfony\Component\HttpFoundation\File\UploadedFile ($path, 'dummy-img.jpg', $mime, null, null, true);
    }

    /**
     * @group meetingTest
     * 
     * test store meetings
     * check the response
     * and check database
     * and check image
     */
    public function testMeetingIsCreatedSuccessfullyWithImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin(10);
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        $now = Carbon::now()->format('Y_m_d');
        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();
        
        $payload = [
            'title' => 'Meeting '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay()->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'address '.$selected_postcode,
            'phone' => '987654321',
            'location_image' => $this->prepareFileUpload('public/storage/uploads/dummy-img.jpg'),
            'location_image_modified' => '1' // 1 if there is image data or update image, 0 if no image data
        ];

        $response = $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );

        unset($payload['location_image']);
        unset($payload['location_image_modified']);
        $this->assertDatabaseHas('meetings', $payload);
        
        // get last data created
        $last_meeting = DummyMeeting::orderBy('id', 'desc')->first();
        $location_image_name = explode('/', $last_meeting->location_image_url);
        // Assert the file was stored
        Storage::disk('public_upload')->assertExists('meetings/'.$location_image_name[1]);
    }

    /**
     * @group meetingTest
     * 
     * test update meeting
     * check the response
     * and check database
     * and check image
     * 
     * update scenario:
     * don't have an image to have an image
     */
    public function testMeetingIsUpdatedSuccessfullyWithImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'title' => 'Meeting Update '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay(5)->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'address '.$selected_postcode,
            'phone' => '987654321',
            'location_image' => $this->prepareFileUpload('public/storage/uploads/dummy-img.jpg'),
            'location_image_modified' => '1' // 1 if there is image data or update image, 0 if no image data
        ];
        $meeting_update = DummyMeeting::find(1);
            
        $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings/'.$meeting_update->id, $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        
        $meeting_after_update = DummyMeeting::find(1);
        unset($payload['location_image']);
        unset($payload['location_image_modified']);
        $payload['id'] = $meeting_after_update->id;
        $this->assertDatabaseHas('meetings', $payload);
        $location_image_name = explode('/', $meeting_after_update->location_image_url);
        // Assert the file was stored
        Storage::disk('public_upload')->assertExists('meetings/'.$location_image_name[1]);
    }

    /**
     * @group meetingTest
     * 
     * test update meeting
     * check the response
     * and check database
     * and check image
     * 
     * update scenario:
     * don't have an image to have an image
     */
    public function testMeetingIsUpdatedSuccessfullyChangeImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // --- for create new meetings with image
        $now = Carbon::now()->format('Y_m_d');
        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();
        
        $payload_create = [
            'title' => 'Meeting Create '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay()->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'Address Create '.$selected_postcode,
            'phone' => '987654321',
            'location_image' => $this->prepareFileUpload('public/storage/uploads/dummy-img.jpg'),
            'location_image_modified' => '1' // 1 if there is image data or update image, 0 if no image data
        ];

        $response_create = $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings', $payload_create)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        unset($payload_create['location_image']);
        unset($payload_create['location_image_modified']);
        $this->assertDatabaseHas('meetings', $payload_create);
        
        // get last data created
        $last_meeting = DummyMeeting::orderBy('id', 'desc')->first();
        $location_image_name = explode('/', $last_meeting->location_image_url);
        // Assert the file was stored
        Storage::disk('public_upload')->assertExists('meetings/'.$location_image_name[1]);
        // --- END for create new meetings with image

        // --- for update the meeting data that has an image
        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'title' => 'Meeting Update '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay(5)->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'Address Update'.$selected_postcode,
            'phone' => '123456789',
            'location_image' => $this->prepareFileUpload('public/storage/uploads/dummy-img.jpg'),
            'location_image_modified' => '1' // 1 if there is image data or update image, 0 if no image data
        ];
  
        $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings/'.$last_meeting->id, $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        
        $meeting_after_update = DummyMeeting::find($last_meeting->id);
        unset($payload['location_image']);
        unset($payload['location_image_modified']);
        $payload['id'] = $meeting_after_update->id;
        $this->assertDatabaseHas('meetings', $payload);
        $location_image_name_after_update = explode('/', $meeting_after_update->location_image_url);
        // Assert the file was stored
        Storage::disk('public_upload')->assertExists('meetings/'.$location_image_name_after_update[1]);
        // Assert the old file was delete
        Storage::disk('public_upload')->assertMissing('meetings/'.$location_image_name[1]);
        // --- END for update the meeting data that has an image
    }

    /**
     * @group meetingTest
     * 
     * test delete meeting 
     * check the response
     * and check database
     * and check image
     */
    public function testMeetingIsDestroyedSuccessfullyWithImage() 
    {
        // -- get user for login
        $user_login = $this->testUserLogin();
        // -- END get user for login

        $this->runSeederCustomerAndMeeting(); // --- run seeder

        // --- for create new meetings with image
        $now = Carbon::now()->format('Y_m_d');
        // array for customer
        $arr_customer = Customer::pluck('id');
        // array for registrant (users)
        $arr_registrant = User::pluck('id');
        // array for location
        $arr_location = [0, 1];
        // array postcode just for test
        $arr_postcode = ['0600000', '0640941', '0600041', '0600042', '0640820', '0600031', '0600001', '0640821', '0600032', '0600002'];
        $selected_postcode = collect($arr_postcode, 1)->random();
        
        $payload = [
            'title' => 'Meeting '.$now,
            'customer' => collect($arr_customer, 1)->random(),
            'registrant' => collect($arr_registrant, 1)->random(),
            'location' => collect($arr_location, 1)->random(),
            'meeting_date' => Carbon::now()->addDay()->format('Y-m-d H:i'),
            'postcode' => collect($arr_postcode, 1)->random(),
            'address' => 'address '.$selected_postcode,
            'phone' => '987654321',
            'location_image' => $this->prepareFileUpload('public/storage/uploads/dummy-img.jpg'),
            'location_image_modified' => '1' // 1 if there is image data or update image, 0 if no image data
        ];

        $response_create = $this->actingAs($user_login, 'sanctum')
            ->postJson('api/meetings', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        unset($payload['location_image']);
        unset($payload['location_image_modified']);
        $this->assertDatabaseHas('meetings', $payload);
        
        // get last data created
        $last_meeting = DummyMeeting::orderBy('id', 'desc')->first();
        $location_image_name = explode('/', $last_meeting->location_image_url);
        // Assert the file was stored
        Storage::disk('public_upload')->assertExists('meetings/'.$location_image_name[1]);
        // --- END for create new meetings with image

        // --- for elete meetings with image
        $this->actingAs($user_login, 'sanctum')
            ->deleteJson('api/meetings/'.$last_meeting->id)
            ->assertExactJson(
                [
                    'data' => 1, // 1 mean success delete data
                    'message' => 'Successfully process the request'
                ]
            );

        $meeting_check = [
            'title' => $last_meeting->title,
            'customer' => $last_meeting->customer,
            'registrant' => $last_meeting->registrant,
            'location' => $last_meeting->location,
            'meeting_date' => $last_meeting->meeting_date,
            'postcode' => $last_meeting->postcode,
            'address' => $last_meeting->address,
            'phone' => $last_meeting->phone
        ];
        $this->assertDatabaseMissing('meetings', $meeting_check);
        // Assert the file was delete
        Storage::disk('public_upload')->assertMissing('meetings/'.$location_image_name[1]);
        // --- END for elete meetings with image
    }
}
