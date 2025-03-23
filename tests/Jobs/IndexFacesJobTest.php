<?php

namespace Tests\Jobs;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Jobs\AssociateFacesJob;
use App\Jobs\IndexFacesJob;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexFacesJobTest extends TestCase
{
    #[Test]
    public function it_tests_index_faces_job()
    {
        /* SETUP */
        Queue::fake(AssociateFacesJob::class);
        $awsCollection = AwsCollection::factory()->create();
        $firstImage = UploadedFile::fake()->image('test_image.jpg');
        $imageTempPath = 'temp/' . Str::uuid() . '.' . $firstImage->getClientOriginalExtension();
        Storage::put($imageTempPath, $firstImage->getContent());
        $imagePaths = [$imageTempPath];
        $user = User::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => generate_external_id($user->id),
        ]);
        $methodName = 'indexFaces';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        IndexFacesJob::dispatchSync($awsCollection->id, $imagePaths, $user);

        /* ASSERT */
        $this->assertDatabaseHas('aws_faces', [
            'user_id' => $user->id,
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
            'external_image_id' => 'test_external_image_id',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace records are created!',
            'event' => ActivityEvent::CREATED->value,
        ]);
        Queue::assertPushed(AssociateFacesJob::class);
        // Clean temp storage if anything left
        Storage::delete(Storage::allFiles('temp'));
    }
}
