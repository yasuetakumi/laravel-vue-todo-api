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
    public function testUserLogin()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 1)->create();
        return $user_login = User::find(1);
    }

    /**
     * @group meetingTest1
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

        // --- run seeder customer
        $this->seed(CustomerSeeder::class);
        // --- END run seeder customer
        // --- run seeder meeting
        $this->seed(DummyMeetingSeeder::class);
        // --- END run seeder meeting

        $response = $this->actingAs($user_login, 'sanctum')
            ->getJson('api/meetings?itemsPerPage=10&page=1&sortBy=&sortDesc=&title=&customer=&location=&meeting_date_start=&meeting_date_end=&registrant=')
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
            // ->assertJsonStructure(
            //     [
            //         'data' => [
            //             'meetings' => [
            //                 'data' => [
            //                     '*' => [
            //                         'id',
            //                         'title',
            //                         'customer' => [
            //                             'id',
            //                             'name',
            //                             'email',
            //                             'phone',
            //                             'website',
            //                             'created_at',
            //                             'updated_at',
            //                         ],
            //                         'meeting_date',
            //                         'location',
            //                         'postcode',
            //                         'address',
            //                         'phone',
            //                         'registrant' => [
            //                             'id',
            //                             'user_role_id',
            //                             'display_name',
            //                             'email',
            //                             'email_verified_at',
            //                             'created_at',
            //                             'updated_at',
            //                             'deleted_at',
            //                             'user_role_name',
            //                             'user_role' => [
            //                                 'id',
            //                                 'name',
            //                                 'label',
            //                                 'created_at',
            //                                 'updated_at',
            //                             ],
            //                         ],
            //                         'location_image_url',
            //                         'created_at',
            //                         'updated_at',
            //                     ]
            //                 ],
            //                 'first_page_url',
            //                 'from',
            //                 'last_page',
            //                 'last_page_url',
            //                 'next_page_url',
            //                 'path',
            //                 'per_page',
            //                 'prev_page_url',
            //                 'to',
            //                 'total',
            //             ],
            //             'formData' => [
            //                 'customers' => [
            //                     '*' => [
            //                         'id',
            //                         'name',
            //                         'email',
            //                         'phone',
            //                         'website',
            //                         'created_at',
            //                         'updated_at',
            //                     ]
            //                 ],
            //                 'locations' => [
            //                     '*' => [
            //                         'value',
            //                         'text'
            //                     ]
            //                 ],
            //                 'registrants' => [
            //                     '*' => [
            //                         'id',
            //                         'display_name',
            //                         'user_role_id',
            //                         'user_role_name',
            //                         'user_role' => [
            //                             'id',
            //                             'name',
            //                             'label',
            //                             'created_at',
            //                             'updated_at',
            //                         ]
            //                     ]
            //                 ],
            //             ]
            //         ],
            //         'message'
            //     ]
            // );
    }
}
