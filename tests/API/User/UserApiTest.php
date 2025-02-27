<?php

namespace Tests\API\User;

use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_tests_show_user_route()
    {
        /* SETUP */
        $user = User::factory()->create();

        /* EXECUTE */
        $response = $this->getJson(route('users.show', ['user' => $user->id]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'description' => $user->description,
                    'views' => $user->views,
                    'avatar' => $user->avatar,
                ]
            ]);
    }

    #[Test]
    public function it_tests_show_user_with_links()
    {
        /* SETUP */
        $user = User::factory()->create();
        Link::factory()->count(2)->create(['user_id' => $user->id]);
        Link::factory()->count(1)->create(['user_id' => $user->id, 'type' => 'email', 'value' => 'test@mail.com']);

        /* EXECUTE */
        $response = $this->getJson(route('users.show', ['user' => $user->id]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'description' => $user->description,
                    'views' => $user->views,
                    'avatar' => $user->avatar,
                    'links' => [
                        [
                            'type' => 'url',
                        ],
                        [
                            'type' => 'url',
                        ],
                        [
                            'type' => 'email',
                            'value' => 'test@mail.com',
                        ],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_show_user_with_links_where_is_visible_false_not_showing()
    {
        /* SETUP */
        $user = User::factory()->create();
        Link::factory()->count(2)->create(['user_id' => $user->id]);
        Link::factory()->count(1)->create(['user_id' => $user->id, 'is_visible' => false, 'type' => 'email', 'value' => 'test@mail.com']);

        /* EXECUTE */
        $response = $this->getJson(route('users.show', ['user' => $user->id]));

        /* ASSERT */
        $response->assertOk()
            ->assertJsonMissing([
                'data' => [
                    'id' => $user->id,
                    'links' => [
                        [
                            'type' => 'url',
                        ],
                        [
                            'type' => 'url',
                        ],
                        [
                            'type' => 'email',
                            'value' => 'test@mail.com',
                        ],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_user_profile_route()
    {
        /* SETUP */
        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('users.profile'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'description' => $user->description,
                    'views' => $user->views,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                ]
            ]);
    }

    #[Test]
    public function it_tests_delete_user_route()
    {
        /* SETUP */
        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson(route('users.delete', ['user' => $user->id, 'password' => $myPassword]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'User deleted successfully!',
                ]
            ]);
    }

    #[Test]
    public function it_tests_update_user_route()
    {
        /* SETUP */
        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');
        $newUserName = 'updated_username';

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson(route('users.update', ['user' => $user->id, 'username' => $newUserName]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $newUserName,
                    'description' => $user->description,
                    'views' => $user->views,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                ]
            ]);
    }

    #[Test]
    public function it_tests_validations_of_update_user_route()
    {
        /* SETUP */
        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');
        $newData = [
            'user' => $user->id,
            'email' => 'rand_text_not_email',
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson(route('users.update', $newData));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'email']);
    }
}
