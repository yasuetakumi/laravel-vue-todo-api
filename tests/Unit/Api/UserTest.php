<?php

namespace Tests\Unit\Api;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use UserRoleSeeder;
use Carbon\Carbon;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group userTest1
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
     * @group userTest1
     * 
     * test store user
     * and check the structure of the response
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
     * @group userTest1
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
     * @group userTest1
     * 
     * test update user
     */
    public function testUpdateUserReturnsCorrectData() {
        $user = User::create(
            [
                'first_name' => $this->faker->firstName,
                'last_name'  => $this->faker->lastName,
                'email'      => $this->faker->email
            ]
        );
        Wallet::create(
            [
                'balance' => 0,
                'user_id' => $user->id
            ]
        );
            
        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email
        ];
            
        $this->json('put', "api/user/$user->id", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(
                [
                    'data' => [
                        'id'         => $user->id,
                        'first_name' => $payload['first_name'],
                        'last_name'  => $payload['last_name'],
                        'email'      => $payload['email'],
                        'created_at' => (string)$user->created_at,
                        'wallet'     => [
                            'id'      => $user->wallet->id,
                            'balance' => $user->wallet->balance
                        ]
                    ]
                ]
            );
    }

    /**
     * @group userTest
     * 
     * test delete user 
     * and check the structure of the response
     * and check database
     */
    public function testUserIsDestroyed() {
    
        $userData = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email
        ];
        $user = User::create(
            $userData
        );
        
        $this->json('delete', "api/user/$user->id")
             ->assertNoContent();

        $this->assertDatabaseMissing('users', $userData);
    }
}
