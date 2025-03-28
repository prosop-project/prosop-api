<?php

namespace Jobs;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Jobs\DeleteUserJob;
use App\Models\AnalysisOperation;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsSimilarityResult;
use App\Models\AwsUser;
use App\Models\Link;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionInterface;
use Mockery\MockInterface;
use MoeMizrak\Rekognition\Data\ResultData\DeleteFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\UserResultData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteUserJobTest extends TestCase
{
    #[Test]
    public function it_tests_delete_user_job_checking_whether_user_links_deleted_successfully()
    {
        /* SETUP */
        $user = User::factory()->create();
        $firstLink = Link::factory()->create(['user_id' => $user->id]);
        $secondLink = Link::factory()->create(['user_id' => $user->id]);

        /* EXECUTE */
        DeleteUserJob::dispatchSync($user);

        /* ASSERT */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('links', ['id' => $firstLink->id]);
        $this->assertDatabaseMissing('links', ['id' => $secondLink->id]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $firstLink->id,
            'model' => Link::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $secondLink->id,
            'model' => Link::class,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::LINK_MODEL_ACTIVITY->value,
            'description' => 'Link is deleted!',
            'subject_type' => Link::class,
            'subject_id' => $firstLink->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::LINK_MODEL_ACTIVITY->value,
            'description' => 'Link is deleted!',
            'subject_type' => Link::class,
            'subject_id' => $secondLink->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }

    #[Test]
    public function it_tests_delete_user_job_checking_whether_related_aws_users_deleted_successfully()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        /*
         * Here we mock the deleteFaces method of AwsRekognitionService class directly instead of the RekognitionClient class,
         * because we make multiple calls to its methods.
         */
        $methodNameDeleteUser = 'deleteUser';
        $methodResponseDeleteUser = new UserResultData();
        $this->mock(AwsRekognitionInterface::class, function (MockInterface $mock) use(
            $methodNameDeleteUser, $methodResponseDeleteUser
        ) {
            $mock->shouldReceive($methodNameDeleteUser)
                ->twice()
                ->andReturn($methodResponseDeleteUser);
        });

        /* EXECUTE */
        DeleteUserJob::dispatchSync($user);

        /* ASSERT */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('aws_users', ['id' => $firstAwsUser->id]);
        $this->assertDatabaseMissing('aws_users', ['id' => $secondAwsUser->id]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $firstAwsUser->id,
            'model' => AwsUser::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $secondAwsUser->id,
            'model' => AwsUser::class,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is deleted!',
            'subject_type' => AwsUser::class,
            'subject_id' => $firstAwsUser->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is deleted!',
            'subject_type' => AwsUser::class,
            'subject_id' => $secondAwsUser->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }

    #[Test]
    public function it_tests_delete_user_job_checking_whether_related_aws_faces_deleted_successfully()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $firstAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $firstAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $firstAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $thirdAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $secondAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $fourthAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $secondAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        /*
         * Here we mock the deleteFaces method of AwsRekognitionService class directly instead of the RekognitionClient class,
         * because we make multiple calls to its methods .
         */
        $methodNameDeleteFaces = 'deleteFaces';
        $methodResponseDeleteFaces = new DeleteFacesResultData(
            deletedFaces: [$firstAwsFace->external_face_id, $secondAwsFace->external_face_id]
        );
        $secondMethodResponseDeleteFaces = new DeleteFacesResultData(
            deletedFaces: [$thirdAwsFace->external_face_id, $fourthAwsFace->external_face_id]
        );
        $methodNameDeleteUser = 'deleteUser';
        $methodResponseDeleteUser = new UserResultData();
        $this->mock(AwsRekognitionInterface::class, function (MockInterface $mock) use(
            $methodNameDeleteFaces, $methodNameDeleteUser, $methodResponseDeleteFaces, $methodResponseDeleteUser, $secondMethodResponseDeleteFaces
        ) {
            $mock->shouldReceive($methodNameDeleteFaces)
                ->twice()
                ->andReturn($methodResponseDeleteFaces, $secondMethodResponseDeleteFaces);

            $mock->shouldReceive($methodNameDeleteUser)
                ->twice()
                ->andReturn($methodResponseDeleteUser);
        });

        /* EXECUTE */
        DeleteUserJob::dispatchSync($user);

        /* ASSERT */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('aws_faces', ['id' => $firstAwsFace->id]);
        $this->assertDatabaseMissing('aws_faces', ['id' => $secondAwsFace->id]);
        $this->assertDatabaseMissing('aws_faces', ['id' => $thirdAwsFace->id]);
        $this->assertDatabaseMissing('aws_faces', ['id' => $fourthAwsFace->id]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $firstAwsFace->id,
            'model' => AwsFace::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $secondAwsFace->id,
            'model' => AwsFace::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $thirdAwsFace->id,
            'model' => AwsFace::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $fourthAwsFace->id,
            'model' => AwsFace::class,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $firstAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $secondAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $thirdAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $fourthAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }

    #[Test]
    public function it_tests_delete_user_job_checking_whether_related_aws_faces_and_aws_similarity_result_deleted_successfully()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $firstAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $firstAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $analysisOperation = AnalysisOperation::factory()->create();
        $firstAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
            'aws_face_id' => $firstAwsFace->id,
        ]);
        $secondAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $firstAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
            'aws_face_id' => $secondAwsFace->id,
        ]);
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $thirdAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $secondAwsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $thirdAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
            'aws_face_id' => $thirdAwsFace->id,
        ]);
        /*
         * Here we mock the deleteFaces method of AwsRekognitionService class directly instead of the RekognitionClient class,
         * because we make multiple calls to its methods .
         */
        $methodNameDeleteFaces = 'deleteFaces';
        $methodResponseDeleteFaces = new DeleteFacesResultData(
            deletedFaces: [$firstAwsFace->external_face_id, $secondAwsFace->external_face_id]
        );
        $secondMethodResponseDeleteFaces = new DeleteFacesResultData(
            deletedFaces: [$thirdAwsFace->external_face_id]
        );
        $methodNameDeleteUser = 'deleteUser';
        $methodResponseDeleteUser = new UserResultData();
        $this->mock(AwsRekognitionInterface::class, function (MockInterface $mock) use(
            $methodNameDeleteFaces, $methodNameDeleteUser, $methodResponseDeleteFaces, $methodResponseDeleteUser, $secondMethodResponseDeleteFaces
        ) {
            $mock->shouldReceive($methodNameDeleteFaces)
                ->twice()
                ->andReturn($methodResponseDeleteFaces, $secondMethodResponseDeleteFaces);

            $mock->shouldReceive($methodNameDeleteUser)
                ->twice()
                ->andReturn($methodResponseDeleteUser);
        });

        /* EXECUTE */
        DeleteUserJob::dispatchSync($user);

        /* ASSERT */
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('aws_similarity_results', ['id' => $firstAwsSimilarityResult->id]);
        $this->assertDatabaseMissing('aws_similarity_results', ['id' => $secondAwsSimilarityResult->id]);
        $this->assertDatabaseMissing('aws_similarity_results', ['id' => $thirdAwsSimilarityResult->id]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $firstAwsSimilarityResult->id,
            'model' => AwsSimilarityResult::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $secondAwsSimilarityResult->id,
            'model' => AwsSimilarityResult::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $thirdAwsSimilarityResult->id,
            'model' => AwsSimilarityResult::class,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $firstAwsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $secondAwsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $thirdAwsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }

    #[Test]
    public function it_tests_delete_user_job_checking_whether_related_subscriptions_deleted_successfully()
    {
        /* SETUP */
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();
        $userD = User::factory()->create();
        // UserB subscribes to UserA
        $firstSubscription = Subscription::factory()->create([
            'user_id' => $userA->id,
            'subscriber_id' => $userB->id
        ]);
        // UserC subscribes to UserA
        $secondSubscription = Subscription::factory()->create([
            'user_id' => $userA->id,
            'subscriber_id' => $userC->id
        ]);
        // UserA subscribes to UserD
        $thirdSubscription = Subscription::factory()->create([
            'user_id' => $userD->id,
            'subscriber_id' => $userA->id
        ]);

        /* EXECUTE */
        DeleteUserJob::dispatchSync($userA);

        /* ASSERT */
        $this->assertDatabaseMissing('users', ['id' => $userA->id]);
        $this->assertDatabaseMissing('subscriptions', [
            'id' => $firstSubscription->id
        ]);
        $this->assertDatabaseMissing('subscriptions', [
            'id' => $secondSubscription->id
        ]);
        $this->assertDatabaseMissing('subscriptions', [
            'id' => $thirdSubscription->id
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $firstSubscription->id,
            'model' => Subscription::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $secondSubscription->id,
            'model' => Subscription::class,
        ]);
        $this->assertDatabaseHas('deleted_models', [
            'key' => $thirdSubscription->id,
            'model' => Subscription::class,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::SUBSCRIPTION_MODEL_ACTIVITY->value,
            'description' => 'Subscription is deleted!',
            'subject_type' => Subscription::class,
            'subject_id' => $firstSubscription->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::SUBSCRIPTION_MODEL_ACTIVITY->value,
            'description' => 'Subscription is deleted!',
            'subject_type' => Subscription::class,
            'subject_id' => $secondSubscription->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::SUBSCRIPTION_MODEL_ACTIVITY->value,
            'description' => 'Subscription is deleted!',
            'subject_type' => Subscription::class,
            'subject_id' => $thirdSubscription->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
    }
}
