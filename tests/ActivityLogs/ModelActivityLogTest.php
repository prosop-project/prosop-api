<?php

namespace Tests\ActivityLogs;

use App\Models\Link;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModelActivityLogTest extends TestCase
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
    public function it_tests_whether_creating_link_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $type = 'email';
        $description = 'test description';
        $value = 'test@mail.com';

        /* EXECUTE */
        $link = Link::factory()->create([
            'type' => $type,
            'description' => $description,
            'value' => $value,
        ]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
        ]);
    }

    #[Test]
    public function it_tests_whether_updating_link_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $type = 'email';
        $description = 'test description';
        $value = 'test@mail.com';
        $link = Link::factory()->create([
            'type' => $type,
            'description' => $description,
            'value' => $value,
        ]);
        $newDescription = 'new description';

        /* EXECUTE */
        $link->update([
            'description' => $newDescription,
        ]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'updated',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->description' => $newDescription,
            'properties->old->description' => $description,
        ]);
    }

    #[Test]
    public function it_tests_whether_deleting_link_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $type = 'email';
        $description = 'test description';
        $value = 'test@mail.com';
        $link = Link::factory()->create([
            'type' => $type,
            'description' => $description,
            'value' => $value,
        ]);

        /* EXECUTE */
        $link->delete();

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'deleted',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->type' => $type,
            'properties->old->value' => $value,
            'properties->old->description' => $description,
        ]);
    }

    #[Test]
    public function it_tests_activity_is_NOT_logged_when_only_click_count_is_changed_since_dontLogIfAttributesChangedOnly_method_added_to_link_model()
    {
        /* SETUP */
        $type = 'email';
        $description = 'test description';
        $value = 'test@mail.com';
        $link = Link::factory()->create([
            'type' => $type,
            'description' => $description,
            'value' => $value,
            'click_count' => 0,
        ]);

        /* EXECUTE */
        $link->increment('click_count');

        /* ASSERT */
        $this->assertDatabaseMissing('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'updated',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
            'properties->attributes->click_count' => 1,
            'properties->old->click_count' => 0,
        ]);
    }

    #[Test]
    public function it_tests_whether_creating_user_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $username = 'new_user';
        $password = 'new_password';
        $email = 'test@mail.com';
        $views = 0;

        /* EXECUTE */
        $newUser = User::factory()->create([
            'username' => $username,
            'password' => $password,
            'views' => $views,
            'email' => $email,
        ]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'User_model_log',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'description' => 'created',
            'properties->attributes->username' => $username,
            'properties->attributes->email' => $email,
            'properties->attributes->views' => $views,
        ]);
    }

    #[Test]
    public function it_tests_whether_updating_user_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $username = 'new_user';
        $password = 'new_password';
        $email = 'test@mail.com';
        $views = 0;
        $newUser = User::factory()->create([
            'username' => $username,
            'password' => $password,
            'views' => $views,
            'email' => $email,
        ]);
        $newUsername = 'another_username';
        $newEmail = 'new@mail.com';

        /* EXECUTE */
        $newUser->update([
            'username' => $newUsername,
            'email' => $newEmail,
        ]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'User_model_log',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'description' => 'updated',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->username' => $newUsername,
            'properties->attributes->email' => $newEmail,
            'properties->old->username' => $username,
            'properties->old->email' => $email,
        ]);
    }

    #[Test]
    public function it_tests_activity_is_NOT_logged_when_only_views_is_changed_since_dontLogIfAttributesChangedOnly_method_added_to_user_model()
    {
        /* SETUP */
        $username = 'new_user';
        $password = 'new_password';
        $email = 'test@mail.com';
        $views = 0;
        $newUser = User::factory()->create([
            'username' => $username,
            'password' => $password,
            'views' => $views,
            'email' => $email,
        ]);

        /* EXECUTE */
        $newUser->increment('views');

        /* ASSERT */
        $this->assertDatabaseMissing('activity_log', [
            'log_name' => 'User_model_log',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'description' => 'updated',
            'properties->attributes->username' => $username,
            'properties->attributes->email' => $email,
            'properties->attributes->views' => 1,
            'properties->old->views' => 0,
        ]);
    }

    #[Test]
    public function it_does_not_log_user_password()
    {
        /* SETUP */
        $username = 'new_user';
        $password = 'new_password';
        $email = 'test@mail.com';
        $views = 0;

        /* EXECUTE */
        $newUser = User::factory()->create([
            'username' => $username,
            'password' => $password,
            'views' => $views,
            'email' => $email,
        ]);

        /* ASSERT */
        $this->assertDatabaseMissing('activity_log', [
            'log_name' => 'User_model_log',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'description' => 'created',
            'properties->attributes->username' => $username,
            'properties->attributes->email' => $email,
            'properties->attributes->views' => $views,
            'properties->attributes->password' => $password,
        ]);
    }

    #[Test]
    public function it_tests_whether_deleting_user_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $username = 'new_user';
        $password = 'new_password';
        $email = 'test@mail.com';
        $views = 0;
        $newUser = User::factory()->create([
            'username' => $username,
            'password' => $password,
            'views' => $views,
            'email' => $email,
        ]);

        /* EXECUTE */
        $newUser->delete();

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'User_model_log',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'description' => 'deleted',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->username' => $username,
            'properties->old->email' => $email,
        ]);
    }

    #[Test]
    public function it_tests_whether_creating_subscription_model_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $newUser = User::factory()->create();

        /* EXECUTE */
        $subscription = Subscription::factory()->create([
            'user_id' => $newUser->id,
            'subscriber_id' => $this->user->id,
        ]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Subscription_model_log',
            'subject_id' => $subscription->id,
            'subject_type' => Subscription::class,
            'description' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->user_id' => $newUser->id,
            'properties->attributes->subscriber_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_tests_whether_unsubscribing_activity_is_being_logged_successfully()
    {
        /* SETUP */
        $newUser = User::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $newUser->id,
            'subscriber_id' => $this->user->id,
        ]);
        $parameters = [
            'user' => $newUser->id,
        ];

        /* EXECUTE */
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('subscriptions.unsubscribe', $parameters));

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Subscription_model_log',
            'subject_id' => $subscription->id,
            'subject_type' => Subscription::class,
            'description' => 'deleted',
            'event' => 'deleted',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->user_id' => $newUser->id,
            'properties->old->subscriber_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_tests_activity_log_clean_request()
    {
        /* SETUP */
        $type = 'email';
        $description = 'test description';
        $value = 'test@mail.com';
        $link = Link::factory()->create([
            'type' => $type,
            'description' => $description,
            'value' => $value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
        ]);
        $role = Role::query()->create(['name' => 'admin']);
        $this->user->assignRole($role->name);
        $parameters = [
            'log_name' => 'Link_model_log',
            'days' => 30,
        ];
        $activity = Activity::query()->where('log_name', 'Link_model_log')->first();
        $activity->update(['created_at' => now()->subDays(50)]);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('activity_log.clean', $parameters));

        /* ASSERT */
        $response->assertJson(['message' => 'Activity log is cleaned!']);
        $this->assertDatabaseMissing('activity_log', [
            'log_name' => 'Link_model_log',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
        ]);
    }
}
