<?php

namespace Tests\Jobs;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Jobs\AssociateFacesJob;
use App\Jobs\IndexFacesJob;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use MoeMizrak\Rekognition\Data\ResultData\DeleteFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\FaceData;
use MoeMizrak\Rekognition\Data\ResultData\FaceRecordData;
use MoeMizrak\Rekognition\Data\ResultData\IndexFacesResultData;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
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

    #[Test]
    public function it_tests_index_faces_job_when_max_faces_per_user_limit_exceeds()
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
        $firstOldAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondOldAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $thirdOldAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $fourthOldAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $fifthOldAwsFace = AwsFace::factory()->create([
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $methodNameIndexFaces = 'indexFaces';
        $methodNameDeleteFaces = 'deleteFaces';
        $methodResponseDeleteFaces = new DeleteFacesResultData();
        $this->mock(AwsRekognitionInterface::class, function (MockInterface $mock) use(
            $methodNameIndexFaces, $methodNameDeleteFaces, $methodResponseDeleteFaces
        ) {
            $mock->shouldReceive($methodNameIndexFaces)
                ->once()
                ->andReturn($this->mockIndexFacesResponse());

            $mock->shouldReceive($methodNameDeleteFaces)
                ->once()
                ->andReturn($methodResponseDeleteFaces);
        });

        /* EXECUTE */
        IndexFacesJob::dispatchSync($awsCollection->id, $imagePaths, $user);

        /* ASSERT */
        $this->assertDatabaseHas('aws_faces', [
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
            'external_image_id' => 'first_test_external_image_id',
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace records are created!',
            'event' => ActivityEvent::CREATED->value,
        ]);
        $this->assertDatabaseMissing('aws_faces', [
            'id' => $firstOldAwsFace->id,
        ]);
        $this->assertDatabaseMissing('aws_faces', [
            'id' => $secondOldAwsFace->id,
        ]);
        $this->assertDatabaseMissing('aws_faces', [
            'id' => $thirdOldAwsFace->id,
        ]);
        $this->assertDatabaseMissing('aws_faces', [
            'id' => $fourthOldAwsFace->id,
        ]);
        $this->assertDatabaseMissing('aws_faces', [
            'id' => $fifthOldAwsFace->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $firstOldAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $secondOldAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $thirdOldAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $fourthOldAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_FACE_MODEL_ACTIVITY->value,
            'description' => 'AwsFace is deleted!',
            'subject_type' => AwsFace::class,
            'subject_id' => $fifthOldAwsFace->id,
            'event' => ActivityEvent::DELETED->value,
        ]);
        Queue::assertPushed(AssociateFacesJob::class);
        // Clean temp storage if anything left
        Storage::delete(Storage::allFiles('temp'));
    }

    /**
     * Mock the indexFaces method of the AWS Rekognition service.
     *
     * @return IndexFacesResultData
     */
    private function mockIndexFacesResponse(): IndexFacesResultData
    {
        // Mock FaceData objects
        $firstFace = new FaceData(
            confidence     : 95.99,
            faceId         : fake()->uuid,
            imageId        : fake()->uuid,
            externalImageId: 'first_test_external_image_id',
        );

        // Mock face records
        $faceRecords = new DataCollection(FaceRecordData::class, [
            new FaceRecordData(face: $firstFace),
        ]);

       // Create mock IndexFacesResultData object
        return new IndexFacesResultData(
            faceModelVersion: '5.0',
            faceRecords: $faceRecords,
        );
    }
}
