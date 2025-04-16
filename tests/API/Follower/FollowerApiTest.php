<?php

namespace Tests\API\Follower;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FollowerApiTest extends TestCase
{
    private string $token = "";
    private User|Authenticatable $user;

    protected function setUp(): void
    {
        parent::setUp();

        $myPassword = 'my_password';
        $this->user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $this->user->username,
            'password' => $myPassword,
        ];
        $this->token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');
    }

    #[Test]
    public function it_checks_if_given_user_is_same_with_authenticated_user_validation_for_follow_request()
    {
        /* SETUP */
        $parameters = [
            'user_id' => $this->user->id,
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('followers.follow', $parameters));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['follow.yourself']);
    }

    #[Test]
    public function it_tests_follow_request()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('followers.follow', $parameters));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user_id' => $parameters['user'],
                    'follower_id' => auth()->id(),
                ]
            ]);
        $this->assertDatabaseHas('followers', [
            'user_id' => $parameters['user'],
            'follower_id' => auth()->id(),
        ]);
    }

    #[Test]
    public function it_tests_unfollow_request()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('followers.follow', $parameters));

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('followers.unfollow', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Follower is removed successfully!',
                ]
            ]);
        $this->assertDatabaseMissing('followers', [
            'user_id' => $parameters['user'],
            'follower_id' => auth()->id(),
        ]);
    }

    #[Test]
    public function it_tests_followers_request_that_is_the_list_of_users_followed_by_provided_user()
    {
        /* SETUP */
        $firstFollower = Follower::factory()->create([
            'user_id' => User::factory(),
            'follower_id' => $this->user->id,
        ]);
        $secondFollower = Follower::factory()->create([
            'user_id' => User::factory(),
            'follower_id' => $this->user->id,
        ]);
        $listParameters = [
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('followers.following', $listParameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $firstFollower->user_id,
                        'follower_id' => $this->user->id,
                    ],
                    [
                        'user_id' => $secondFollower->user_id,
                        'follower_id' => $this->user->id,
                    ],
                ]
            ]);
        $this->assertDatabaseHas('followers', [
            'user_id' => $firstFollower->user_id,
            'follower_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('followers', [
            'user_id' => $secondFollower->user_id,
            'follower_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_tests_followers_request_that_is_the_list_of_users_that_followed_to_provided_user()
    {
        /* SETUP */
        $firstUser = User::factory()->create();
        Follower::factory()->create([
            'user_id' => $this->user->id,
            'follower_id' => $firstUser->id,
        ]);
        $secondUser = User::factory()->create();
        Follower::factory()->create([
            'user_id' => $this->user->id,
            'follower_id' => $secondUser->id,
        ]);
        $listParameters = [
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('followers.followers', $listParameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $this->user->id,
                        'follower_id' => $firstUser->id,
                    ],
                    [
                        'user_id' => $this->user->id,
                        'follower_id' => $secondUser->id,
                    ],
                ]
            ]);
        $this->assertDatabaseHas('followers', [
            'user_id' => $this->user->id,
            'follower_id' => $firstUser->id,
        ]);
        $this->assertDatabaseHas('followers', [
            'user_id' => $this->user->id,
            'follower_id' => $secondUser->id,
        ]);
    }

    #[Test]
    public function it_tests_follow_request_cannot_follow_same_user()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];

        /* EXECUTE */
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('followers.follow', $parameters));
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('followers.follow', $parameters));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['follow.unique']);
    }
}
