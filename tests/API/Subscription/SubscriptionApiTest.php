<?php

namespace Tests\API\Subscription;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
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
    public function it_checks_if_given_user_is_same_with_authenticated_user_validation_for_subscribe_request()
    {
        /* SETUP */
        $parameters = [
            'user_id' => $this->user->id,
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('subscriptions.subscribe', $parameters));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subscribe.yourself']);
    }

    #[Test]
    public function it_tests_subscribe_request()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('subscriptions.subscribe', $parameters));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user_id' => $parameters['user'],
                    'subscriber_id' => auth()->id(),
                ]
            ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $parameters['user'],
            'subscriber_id' => auth()->id(),
        ]);
    }

    #[Test]
    public function it_tests_unsubscribe_request()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('subscriptions.subscribe', $parameters));

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('subscriptions.unsubscribe', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Subscription is removed successfully!',
            ]);
        $this->assertDatabaseMissing('subscriptions', [
            'user_id' => $parameters['user'],
            'subscriber_id' => auth()->id(),
        ]);
    }

    #[Test]
    public function it_tests_subscriptions_request_that_is_the_list_of_users_subscribed_by_provided_user()
    {
        /* SETUP */
        $firstSubscription = Subscription::factory()->create([
            'user_id' => User::factory(),
            'subscriber_id' => $this->user->id,
        ]);
        $secondSubscription = Subscription::factory()->create([
            'user_id' => User::factory(),
            'subscriber_id' => $this->user->id,
        ]);
        $listParameters = [
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('subscriptions.list', $listParameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $firstSubscription->user_id,
                        'subscriber_id' => $this->user->id,
                    ],
                    [
                        'user_id' => $secondSubscription->user_id,
                        'subscriber_id' => $this->user->id,
                    ],
                ]
            ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $firstSubscription->user_id,
            'subscriber_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $secondSubscription->user_id,
            'subscriber_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_tests_subscribers_request_that_is_the_list_of_users_that_subscribed_to_provided_user()
    {
        /* SETUP */
        $firstUser = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscriber_id' => $firstUser->id,
        ]);
        $secondUser = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'subscriber_id' => $secondUser->id,
        ]);
        $listParameters = [
            'user' => $this->user->id,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('subscriptions.subscribers', $listParameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $this->user->id,
                        'subscriber_id' => $firstUser->id,
                    ],
                    [
                        'user_id' => $this->user->id,
                        'subscriber_id' => $secondUser->id,
                    ],
                ]
            ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscriber_id' => $firstUser->id,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $this->user->id,
            'subscriber_id' => $secondUser->id,
        ]);
    }

    #[Test]
    public function it_tests_subscribe_request_cannot_subscribe_same_user()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $parameters = [
            'user' => $newUser->id,
        ];

        /* EXECUTE */
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('subscriptions.subscribe', $parameters));
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('subscriptions.subscribe', $parameters));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subscribe.unique']);
    }
}
