<?php

namespace Tests\ActivityLogs;

use App\Actions\Recognition\CreateAwsFaceAction;
use App\Actions\Recognition\DeleteFacesAction;
use App\Actions\Recognition\UpdateAwsUserAction;
use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\Link;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ActivityLog\CreateAwsFaceModelActivityService;
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
            'log_name' => 'Link_model_activity',
            'description' => 'Link is created!',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'event' => ActivityEvent::CREATED->value,
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
            'log_name' => 'Link_model_activity',
            'description' => 'Link is updated!',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'event' => ActivityEvent::UPDATED->value,
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
            'log_name' => 'Link_model_activity',
            'description' => 'Link is deleted!',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'event' => ActivityEvent::DELETED->value,
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
            'log_name' => 'Link_model_activity',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'event' => ActivityEvent::UPDATED->value,
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
            'log_name' => 'User_model_activity',
            'description' => 'User is created!',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => ActivityEvent::CREATED->value,
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
            'log_name' => 'User_model_activity',
            'description' => 'User is updated!',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => ActivityEvent::UPDATED->value,
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
            'log_name' => 'User_model_activity',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => ActivityEvent::UPDATED->value,
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
            'log_name' => 'User_model_activity',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => ActivityEvent::CREATED->value,
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
            'log_name' => 'User_model_activity',
            'description' => 'User is deleted!',
            'subject_id' => $newUser->id,
            'subject_type' => User::class,
            'event' => ActivityEvent::DELETED->value,
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
            'log_name' => 'Subscription_model_activity',
            'description' => 'Subscription is created!',
            'subject_id' => $subscription->id,
            'subject_type' => Subscription::class,
            'event' => ActivityEvent::CREATED->value,
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
            'log_name' => 'Subscription_model_activity',
            'subject_id' => $subscription->id,
            'subject_type' => Subscription::class,
            'description' => 'Subscription is deleted!',
            'event' => ActivityEvent::DELETED->value,
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
            'log_name' => 'Link_model_activity',
            'description' => 'Link is created!',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'event' => ActivityEvent::CREATED->value,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
        ]);
        $role = Role::query()->create(['name' => 'admin']);
        $this->user->assignRole($role->name);
        $days = 30;
        $logName = 'Link_model_activity';
        $parameters = [
            'log_name' => $logName,
            'days' => $days,
        ];
        $activity = Activity::query()->where('log_name', $logName)->first();
        $activity->update(['created_at' => $this->now->subDays(50)]);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('activity_log.clean', $parameters));

        /* ASSERT */
        $response->assertJson([
            'data' => [
                'message' => 'Activity log is cleaned!'
            ]
        ]);
        $this->assertDatabaseMissing('activity_log', [
            'log_name' => 'Link_model_activity',
            'subject_id' => $link->id,
            'subject_type' => Link::class,
            'description' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->type' => $type,
            'properties->attributes->description' => $description,
            'properties->attributes->value' => $value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::ACTIVITYLOG_CLEAN_COMMAND_ACTIVITY->value,
            'description' => 'Activity log is cleaned!',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'event' => ActivityEvent::ACTIVITYLOG_CLEAN_COMMAND->value,
            'properties->days' => $days,
            'properties->log_name' => $logName,
        ]);
    }

    #[Test]
    public function it_tests_activity_log_for_update_aws_user_action()
    {
        /* SETUP */
        $awsUser = AwsUser::factory()->create();
        $externalUserStatus = 'test_status';
        $action = new UpdateAwsUserAction();

        /* EXECUTE */
        $action->handle($awsUser, $externalUserStatus);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is updated!',
            'subject_id' => $awsUser->id,
            'subject_type' => AwsUser::class,
            'causer_id' => $this->user->id,
            'event' => ActivityEvent::UPDATED->value,
            'properties->attributes->external_user_status' => 'test_status',
            'properties->old->external_user_status' => null,
        ]);
    }

    #[Test]
    public function it_tests_activity_log_for_create_aws_face_action_which_uses_inserts_that_bypass_default_activity_log()
    {
        /* SETUP */
        $awsUser = AwsUser::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $firstExternalFaceId = fake()->uuid();
        $secondExternalFaceId = fake()->uuid();
        $confidence = 95.99;
        $confidenceSecond = 91.99;
        $externalImageId = 'test_image_id';
        $externalImageIdSecond = 'test_image_id_1';
        $imageId = fake()->uuid();
        $imageIdSecond = fake()->uuid();
        $faceParams = [
            [
                'user_id' => $this->user->id,
                'aws_user_id' => $awsUser->id,
                'aws_collection_id' => $awsCollection->id,
                'external_face_id' => $firstExternalFaceId,
                'confidence' => $confidence,
                'external_image_id' => $externalImageId,
                'image_id' => $imageId,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
            [
                'user_id' => $this->user->id,
                'aws_user_id' => $awsUser->id,
                'aws_collection_id' => $awsCollection->id,
                'external_face_id' => $secondExternalFaceId,
                'confidence' => $confidenceSecond,
                'external_image_id' => $externalImageIdSecond,
                'image_id' => $imageIdSecond,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ],
        ];
        $activityService =  app(CreateAwsFaceModelActivityService::class);
        $action = new CreateAwsFaceAction($activityService);

        /* EXECUTE */
        $action->handle($faceParams);

        /* ASSERT */
        $this->assertDatabaseHas('aws_faces', [
            'user_id' => $this->user->id,
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
            'external_face_id' => $firstExternalFaceId,
            'confidence' => $confidence,
            'external_image_id' => $externalImageId,
            'image_id' => $imageId,
        ]);
        $this->assertDatabaseHas('aws_faces', [
            'user_id' => $this->user->id,
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
            'external_face_id' => $secondExternalFaceId,
            'confidence' => $confidenceSecond,
            'external_image_id' => $externalImageIdSecond,
            'image_id' => $imageIdSecond,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsFace_model_activity',
            'description' => 'AwsFace records are created!',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'event' => ActivityEvent::CREATED->value,
        ]);
    }

    #[Test]
    public function it_tests_whether_delete_face_action_triggers_activity_log_succesfully()
    {
        /* SETUP */
        $firstAwsFace = AwsFace::factory()->create();
        $secondAwsFace = AwsFace::factory()->create();
        $action = new DeleteFacesAction();

        /* EXECUTE */
        $action->handle([$firstAwsFace->id, $secondAwsFace->id]);

        /* ASSERT */
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsFace_model_activity',
            'description' => 'AwsFace is deleted!',
            'subject_id' => $firstAwsFace->id,
            'subject_type' => AwsFace::class,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsFace_model_activity',
            'description' => 'AwsFace is deleted!',
            'subject_id' => $secondAwsFace->id,
            'subject_type' => AwsFace::class,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }
}
