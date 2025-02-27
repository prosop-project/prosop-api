<?php

namespace Tests\API\Auth;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Models\User;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_tests_register_request()
    {
        /* SETUP */
        $newUser = [
            'name' => fake()->name,
            'username' => fake()->userName,
            'description' => fake()->sentence,
            'password' => fake()->password(8, 32),
            'email' => fake()->email,
        ];

        /* EXECUTE */
        $response = $this->postJson(route('auth.register', $newUser));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => $newUser['name'],
                        'username' => $newUser['username'],
                        'description' => $newUser['description'],
                        'email' => $newUser['email'],
                    ],
                    'message' => 'User registered successfully!',
                    'token' => Arr::get($response, 'data.token')
                ]
            ]);
    }

    #[Test]
    public function it_tests_validations_for_register_request()
    {
        /* SETUP */
        $newUser = [
            'name' => fake()->name,
            'description' => fake()->sentence,
            'email' => 'rand_text_not_email',
        ];

        /* EXECUTE */
        $response = $this->postJson(route('auth.register', $newUser));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password', 'email']);
    }

    #[Test]
    public function it_tests_login_request_and_retrieves_token()
    {
        /* SETUP */
        $username = fake()->userName;
        $password = fake()->password(8, 32);
        $newUser = [
            'name' => fake()->name,
            'username' => $username,
            'description' => fake()->sentence,
            'password' => $password,
            'email' => fake()->email,
        ];
        $this->postJson(route('auth.register', $newUser));
        $existingUser = [
            'username' => $username,
            'password' => $password,
        ];

        /* EXECUTE */
        $response = $this->postJson(route('auth.login', $existingUser));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => $newUser['name'],
                        'username' => $newUser['username'],
                        'description' => $newUser['description'],
                        'email' => $newUser['email'],
                    ],
                    'message' => 'Login successful!',
                    'token' => Arr::get($response, 'data.token')
                ],
            ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::LOGIN_USER_ACTIVITY->value,
            'description' => 'User is logged in!',
            'event' => ActivityEvent::LOGIN->value,
            'causer_type' => User::class,
            'causer_id' => auth()->id(),
        ]);
    }

    #[Test]
    public function it_tests_login_request_validation_required_for_username_and_password()
    {
        /* SETUP */
        $username = fake()->userName;
        $password = fake()->password(8, 32);
        $newUser = [
            'name' => fake()->name,
            'username' => $username,
            'description' => fake()->sentence,
            'password' => $password,
            'email' => fake()->email,
        ];
        $this->postJson(route('auth.register', $newUser));
        $existingUser = [];

        /* EXECUTE */
        $response = $this->postJson(route('auth.login', $existingUser));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'username',
                'password',
            ]);
    }

    #[Test]
    public function it_tests_logout_request()
    {
        /* SETUP */
        $username = fake()->userName;
        $password = fake()->password(8, 32);
        $newUser = [
            'name' => fake()->name,
            'username' => $username,
            'description' => fake()->sentence,
            'password' => $password,
            'email' => fake()->email,
        ];
        $this->postJson(route('auth.register', $newUser));
        $existingUser = [
            'username' => $username,
            'password' => $password,
        ];
        $this->postJson(route('auth.login', $existingUser));

        /* EXECUTE */
        $response = $this->postJson(route('auth.logout'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Successfully logged out!',
                ],
            ]);
    }
}
