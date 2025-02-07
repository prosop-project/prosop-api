<?php

namespace Tests\API;

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
}
