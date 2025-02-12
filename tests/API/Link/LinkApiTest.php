<?php

namespace API\Link;

use App\Models\Link;
use App\Models\User;
use Illuminate\Support\Arr;
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
            ->assertJsonValidationErrors(['value', 'is_visible']);
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

    #[Test]
    public function it_tests_delete_link_request()
    {
        /* SETUP */
        $newLink = [
            'type' => 'email',
            'description' => 'test description',
            'value' => 'test@email.com',
            'is_visible' => true,
        ];
         $link = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $newLink));

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('links.delete', ['link' => Arr::get($link->json(), 'data.id')]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Link deleted successfully!',
            ]);
    }

    #[Test]
    public function it_tests_update_link_request()
    {
        /* SETUP */
        $linkData = [
            'type' => 'email',
            'description' => 'test description',
            'value' => 'test@email.com',
            'is_visible' => true,
        ];
        $link = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $linkData));
        $newLinkData = [
            'type' => 'email',
            'description' => 'new test description',
            'value' => 'neewtest@email.com',
            'is_visible' => false,
            'link' => Arr::get($link->json(), 'data.id'),
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson(route('links.update', $newLinkData));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Link updated successfully!',
            ]);
    }

    #[Test]
    public function it_tests_update_link_request_where_if_value_is_changed_click_count_set_to_zero()
    {
        /* SETUP */
        $linkData = [
            'type' => 'email',
            'description' => 'test description',
            'value' => 'test@email.com',
            'is_visible' => true,
            'click_count' => 10,
        ];
        $link = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('links.create', $linkData));
        $newLinkData = [
            'type' => 'email',
            'description' => 'new test description',
            'value' => 'modifiedtest@email.com',
            'is_visible' => false,
            'link' => Arr::get($link->json(), 'data.id'),
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson(route('links.update', $newLinkData));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'message' => 'Link updated successfully!',
            ]);
        $modifiedLink = Link::query()->where('id', Arr::get($link->json(), 'data.id'))->first();
        $this->assertEquals(0, $modifiedLink->click_count);
    }
}
