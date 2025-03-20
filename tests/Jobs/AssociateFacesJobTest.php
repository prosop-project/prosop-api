<?php

namespace Tests\Jobs;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Enums\ExternalUserStatus;
use App\Jobs\AssociateFacesJob;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssociateFacesJobTest extends TestCase
{
    #[Test]
    public function it_tests_associate_faces_job()
    {
        /* SETUP */
        $methodName = 'associateFaces';
        $this->mockRekognitionClient($methodName);
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $externalUserId = generate_external_id($user->id);
        $awsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => $externalUserId,
        ]);
        $externalFaceIds = ['test_external_face_id'];

        /* EXECUTE */
        AssociateFacesJob::dispatchSync($awsCollection, $awsUser, $externalFaceIds);

        /* ASSERT */
        $this->assertDatabaseHas('aws_users', [
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => $externalUserId,
            'external_user_status' => ExternalUserStatus::UPDATING->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is updated!',
            'event' => ActivityEvent::UPDATED->value,
            'subject_id' => $awsUser->id,
            'subject_type' => AwsUser::class,
        ]);
    }
}
