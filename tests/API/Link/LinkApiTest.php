<?php

namespace API\Link;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LinkApiTest extends TestCase
{
    private string $token = "";

    protected function setUp(): void
    {
        parent::setUp();

        $myPassword = 'my_password';
        $user = User::factory()->create(['username' => 'test_user', 'password' => Hash::make($myPassword)]);
        $existingUser = [
            'username' => $user->username,
            'password' => $myPassword,
        ];
        $this->token = $this->postJson(route('auth.login'), $existingUser)->json('data.token');
    }

    #[Test]
    public function it_tests_is_visible_and_value_are_required_validation_for_create_link_request()
    {
        /* SETUP */
        $newLink = [
            'type' => 'email',
            'description' => 'test description',
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors('value')
            ->assertJsonValidationErrors('is_visible');
    }

    #[Test]
    public function it_tests_create_link_request()
    {
        /* SETUP */
        $newLink = [
            'type' => 'email',
            'description' => 'test description',
            'value' => 'test@email.com',
            'is_visible' => true,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'type' => $newLink['type'],
                    'description' => $newLink['description'],
                    'value' => $newLink['value'],
                    'is_visible' => $newLink['is_visible'],
                ]
            ]);
    }

    #[Test]
    public function it_tests_max_number_of_links_can_be_attached_to_user()
    {
        /* SETUP */
        $newLink = [
            'type' => 'email',
            'description' => 'test description',
            'value' => 'test@email.com',
            'is_visible' => true,
        ];

        /* EXECUTE */
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));


        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors('link');
    }
}
