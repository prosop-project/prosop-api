<?php

namespace Tests\Jobs;

use App\Enums\ExternalUserStatus;
use App\Enums\Status;
use App\Jobs\SearchUsersByImageJob;
use App\Models\AnalysisOperation;
use App\Models\AwsCollection;
use App\Models\AwsUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchUsersByImageJobTest extends TestCase
{
    #[Test]
    public function it_tests_search_users_by_image_job()
    {
        /* SETUP */
        $methodName = 'searchUsersByImage';
        $this->mockRekognitionClient($methodName);
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'metadata' => ['max_users' => 10],
        ]);
        $awsUser = AwsUser::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => 'test_user_id',
        ]);
        $firstImage = UploadedFile::fake()->image('test_image.jpg');
        $imageTempPath = 'temp/' . Str::uuid() . '.' . $firstImage->getClientOriginalExtension();
        Storage::put($imageTempPath, $firstImage->getContent());

        /* EXECUTE */
        SearchUsersByImageJob::dispatchSync($analysisOperation, $imageTempPath);

        /* ASSERT */
        $awsUser->refresh();
        $this->assertEquals(ExternalUserStatus::ACTIVE->value, $awsUser->external_user_status);
        $this->assertDatabaseHas('aws_similarity_results', [
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $awsUser->id,
            'similarity' => 99.88,
        ]);
        $this->assertDatabaseHas('analysis_operations', [
            'id' => $analysisOperation->id,
            'status' => Status::COMPLETED->value,
        ]);
        // Clean temp storage if anything left
        Storage::delete(Storage::allFiles('temp'));
    }
}
