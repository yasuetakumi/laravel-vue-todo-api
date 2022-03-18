<?php

namespace Tests\Unit\Api;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use UserRoleSeeder;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * to run phpunit test :
     * vendor/bin/phpunit
     * 
     * with spesific group :
     * vendor/bin/phpunit --group userTest
    */

    /**
     * @group userTest
     * 
     * test get user list 
     * and check the structure of the response
     */
    public function testUserList()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 20)->create();
        $user = User::find(1);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('api/users?itemsPerPage=10&page=1&sortBy=&sortDesc=')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(
                [
                    'data' => [
                            'users' => [
                                'data' => [
                                    '*' => [
                                        'id',
                                        'user_role_id',
                                        'display_name',
                                        'email',
                                        'email_verified_at',
                                        'created_at',
                                        'updated_at',
                                        'deleted_at',
                                        'label',
                                        'user_role_name',
                                        'user_role' => [
                                            'id',
                                            'name',
                                            'label',
                                            'created_at',
                                            'updated_at',
                                        ],
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
                            'formData'
                    ],
                    'message'
                ]
            );
    }

    /**
     * @group userTest
     * @group userTestList
     * 
     * test get user list 
     * filter by users->user_role_id
     * and check the structure of the response
     */
    public function testUserListFilterUserRoleId()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 20)->create();
        $user = User::find(1);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('api/users?itemsPerPage=10&page=1&sortBy=&sortDesc=&userRole=1') // test filter by userRole=1 (Administrator)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'user_role_id' => '1' // check data with user_role_id = 1
                ]
            )
            ->assertJsonMissing(
                [
                    'user_role_id' => '2' // check no data with user_role_id = 2
                ]
            );
    }

    /**
     * @group userTest
     * @group userTestList
     * 
     * test get user list 
     * filter by users->display_name
     * and check the structure of the response
     */
    public function testUserListFilterDisplayName()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 20)->create();
        $user = User::find(1);
        $user_check = User::find(2);
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('api/users?itemsPerPage=10&page=1&sortBy=&sortDesc=&name='.$user->display_name) 
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'display_name' => $user->display_name
                ]
            )
            ->assertJsonMissing(
                [
                    'display_name' => $user_check->display_name
                ]
            );;
    }

    /**
     * @group userTest
     * @group userTestList
     * 
     * test get user list 
     * filter by users->email
     * and check the structure of the response
     */
    public function testUserListFilterEmail()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 20)->create();
        $user = User::find(1);
        $user_check = User::find(2);
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('api/users?itemsPerPage=10&page=1&sortBy=&sortDesc=&email='.$user->email) 
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(
                [
                    'email' => $user->email
                ]
            )
            ->assertJsonMissing(
                [
                    'email' => $user_check->email
                ]
            );;
    }

    /**
     * @group userTest
     * 
     * test store user
     * check the response
     * and check database
     */
    public function testUserIsCreatedSuccessfully() 
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 1)->create();
        $user = User::find(1);

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'email'         => 'user_test_'.$now.'@mail.com',
            'display_name'  => 'User Test '.$now,
            'password'      => '12345678'
        ];

        $this->actingAs($user, 'sanctum')
            ->json('post', 'api/users', $payload)
            ->assertStatus(Response::HTTP_OK) // need confirmation is 201 or 200
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
        );

        unset($payload['password']);
        $this->assertDatabaseHas('users', $payload);
    }

    /**
     * @group userTest
     * 
     * test show user in the correct state
     */
    public function testUserIsShownCorrectly()
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 1)->create();
        $user = User::first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('api/users/1')
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => [
                        'id' => $user->id,
                        'user_role_id' => $user->user_role_id,
                        'display_name' => $user->display_name,
                        'email' => $user->email,
                        'email_verified_at' => is_null($user->email_verified_at) ? $user->email_verified_at : $user->email_verified_at->format('Y-m-d H:i:s'),
                        'created_at' => is_null($user->created_at) ? $user->created_at : $user->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => is_null($user->updated_at) ? $user->updated_at : $user->updated_at->format('Y-m-d H:i:s'),
                        'deleted_at' => is_null($user->deleted_at) ? $user->deleted_at : $user->deleted_at->format('Y-m-d H:i:s'),
                        'user_role_name' => $user->user_role_name,
                        'user_role'     => [
                            'id' => $user->user_role->id,
                            'name' => $user->user_role->name,
                            'label' => $user->user_role->label,
                            'created_at' => is_null($user->user_role->created_at) ? $user->user_role->created_at : $user->user_role->created_at->format('Y-m-d H:i:s'),
                            'updated_at' => is_null($user->user_role->updated_at) ? $user->user_role->updated_at : $user->user_role->updated_at->format('Y-m-d H:i:s'),
                        ]
                    ],
                    'message' => 'Successfully process the request'
                ]
            );
    }

    /**
     * @group userTest
     * 
     * test update user
     * check the response
     * and check database
     */
    public function testUserIsUpdatedSuccessfully() {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 10)->create();
        $user = User::first();

        $now = Carbon::now()->format('Y_m_d');
        $payload = [
            'email'         => 'user_test_'.$now.'@mail.com',
            'display_name'  => 'User Test '.$now,
            'password'      => '12345678'
        ];
            
        $this->actingAs($user, 'sanctum')
            ->postJson('api/users/2', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => '',
                    'message' => 'Successfully process the request'
                ]
            );
        
        unset($payload['password']);
        $payload['id'] = 2;
        $this->assertDatabaseHas('users', $payload);
    }

    /**
     * @group userTest
     * 
     * test delete user 
     * check the response
     * and check database
     */
    public function testUserIsDestroyedSuccessfully() 
    {
        $this->seed(UserRoleSeeder::class);
        $create = factory(User::class, 10)->create();
        $user = User::first();

        $user_to_delete = User::find(10);
        
        $this->actingAs($user, 'sanctum')
            ->deleteJson('api/users/'.$user_to_delete->id)
            ->assertExactJson(
                [
                    'data' => 1, // 1 mean success delete data
                    'message' => 'Successfully delete user'
                ]
            );

        $user_check = [
            'email'         => $user_to_delete->email,
            'display_name'  => $user_to_delete->display_name
        ];
        $this->assertSoftDeleted('users', $user_check);
    }
}
