<?php

namespace Tests\API\Auth;

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
            'password' => fake()->password,
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
    public function it_tests_login_request_and_retrieves_token()
    {
        /* SETUP */
        $username = fake()->userName;
        $password = fake()->password;
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
    }

    #[Test]
    public function it_tests_logout_request()
    {
        /* SETUP */
        $username = fake()->userName;
        $password = fake()->password;
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
                'message' => 'Successfully logged out!',
            ]);
    }
}
