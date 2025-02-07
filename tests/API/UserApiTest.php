<?php

namespace Tests\API;

use App\Models\Link;
use App\Models\User;
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
}
