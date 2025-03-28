<?php

namespace Tests\Services\Recognition;

use App\Jobs\IndexFacesJob;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\User;
use App\Services\Recognition\AwsRekognitionInterface;
use App\Services\Recognition\AwsRekognitionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use MoeMizrak\Rekognition\Data\ImageData;
use MoeMizrak\Rekognition\Data\ResultData\AssociateFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\DeleteCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\IndexFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListCollectionsResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListFacesResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListUsersResultData;
use MoeMizrak\Rekognition\Data\ResultData\SearchedFaceData;
use MoeMizrak\Rekognition\Data\ResultData\SearchUsersByImageResultData;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class AwsRekognitionServiceTest extends TestCase
{
    private AwsRekognitionService $awsRekognitionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->awsRekognitionService = app(AwsRekognitionInterface::class);
    }

    /**
     * Assertions for metadata such as status code, effective uri, and headers.
     *
     * @param mixed $response
     *
     * @return void
     */
    private function metadataAssertions(mixed $response): void
    {
        $this->assertEquals(200, $response->metadata->statusCode);
        $this->assertNotNull($response->metadata->effectiveUri);
        $this->assertIsArray($response->metadata->headers);
    }

    #[Test]
    public function it_tests_aws_rekognition_create_collection_request()
    {
        /* SETUP */
        $collectionId = 'test_collection_id_0';
        $validatedRequest = [
            'external_collection_id' => $collectionId,
        ];
        $methodName = 'createCollection';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->createCollection($validatedRequest);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertNotNull($response->collectionArn);
        $this->assertNotNull($response->faceModelVersion);
        $this->assertEquals(200, $response->statusCode);
        $this->assertStringContainsString($collectionId, $response->collectionArn);
    }

    #[Test]
    public function it_tests_aws_rekognition_list_collections_request()
    {
        /* SETUP */
        $validatedRequest = [
            'max_results' => 10,
        ];
        $methodName = 'listCollections';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->listExternalCollections($validatedRequest);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            ListCollectionsResultData::class,
            $response
        );
        $this->assertEquals(
            ['test_collection_id_0', 'test_collection_id_1'],
            $response->collectionIds
        );
        $this->assertNotNull($response->collectionIds);
        $this->assertNotNull($response->faceModelVersions);
    }

    #[Test]
    public function it_tests_aws_rekognition_delete_collection_request()
    {
        /* SETUP */
        $deleteCollectionId = 'test_collection_id';
        $methodName = 'deleteCollection';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->deleteCollection($deleteCollectionId);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            DeleteCollectionResultData::class,
            $response
        );
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function it_tests_aws_rekognition_create_user_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $validatedRequest = [
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => 'test_user_id',
        ];
        $methodName = 'createUser';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->createUser($validatedRequest);

        /* ASSERT */
        $this->metaDataAssertions($response);
    }

    #[Test]
    public function it_tests_aws_rekognition_delete_user_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'aws_collection_id' => $awsCollection->id,
        ]);
        $methodName = 'deleteUser';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->deleteUser($awsUser);

        /* ASSERT */
        $this->metaDataAssertions($response);
    }

    #[Test]
    public function it_tests_aws_rekognition_list_aws_users_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $validatedRequest = [
            'aws_collection_id' => $awsCollection->id,
            'max_results' => 20,
        ];
        $methodName = 'listUsers';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->listExternalAwsUsers($validatedRequest);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            ListUsersResultData::class,
            $response
        );
        $this->assertNotNull($response->users);
    }

    #[Test]
    public function it_tests_aws_rekognition_index_faces_request()
    {
        /* SETUP */
        $externalCollectionId = 'test_collection_id';
        $image = UploadedFile::fake()->image('test_image.jpg');
        $imageData = new ImageData(
            bytes: base64_encode($image->getContent())
        );
        $user = User::factory()->create();
        $methodName = 'indexFaces';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->indexFaces($externalCollectionId, $imageData, $user);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            IndexFacesResultData::class,
            $response
        );
        $this->assertNotNull($response->faceModelVersion);
        $this->assertNotNull($response->faceRecords);
        $this->assertInstanceOf(DataCollection::class, $response->faceRecords);
        $this->assertNotNull($response->unindexedFaces);
        $this->assertInstanceOf(DataCollection::class, $response->unindexedFaces);
    }

    #[Test]
    public function it_tests_aws_rekognition_associate_faces_request()
    {
        /* SETUP */
        $methodName = 'associateFaces';
        $this->mockRekognitionClient($methodName);
        $externalCollectionId = 'test_collection_id';
        $externalUserId = 'test_user_id';
        $externalFaceIds = ['8e2ad714-4d23-43c0-b9ad-9fab136bef13', 'ed49afb4-b45b-468e-9614-d652c924cd4a'];

        /* EXECUTE */
        $response = $this->awsRekognitionService->associateFaces($externalCollectionId, $externalFaceIds, $externalUserId);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            AssociateFacesResultData::class,
            $response
        );
        $this->assertNotNull($response->associatedFaces);
        $this->assertInstanceOf(DataCollection::class, $response->associatedFaces);
        $this->assertNotNull($response->unsuccessfulFaceAssociations);
        $this->assertInstanceOf(DataCollection::class, $response->unsuccessfulFaceAssociations);
        $this->assertEquals("UPDATING", $response->userStatus);
    }

    #[Test]
    public function it_tests_process_faces_request()
    {
        /* SETUP */
        Queue::fake();
        $user = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $validatedRequest = [
            'aws_collection_id' => $awsCollection->id,
            'images' => [
                UploadedFile::fake()->image('test_image.jpg'),
                UploadedFile::fake()->image('test_image_2.jpg'),
            ],
        ];

        /* EXECUTE */
        $this->awsRekognitionService->processFaces($validatedRequest, $user);

        /* ASSERT */
        Queue::assertPushed(IndexFacesJob::class);
        // Clean temp storage
        Storage::delete(Storage::allFiles('temp'));
    }

    #[Test]
    public function it_tests_aws_rekognition_list_faces_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'user_id' => $user->id,
        ]);
        $firstAwsFace = AwsFace::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'aws_user_id' => $awsUser->id,
        ]);
        $secondAwsFace = AwsFace::factory()->create([
            'aws_collection_id' => $awsCollection->id,
            'aws_user_id' => $awsUser->id,
        ]);
        $validatedRequest = [
            'aws_collection_id' => $awsCollection->id,
            'user_id' => $user->id,
            'aws_face_ids' => [$firstAwsFace->id, $secondAwsFace->id],
            'max_results' => 20,
        ];
        $methodName = 'listFaces';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->listExternalFaces($validatedRequest);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            ListFacesResultData::class,
            $response
        );
        $this->assertNotNull($response->faces);
    }

    #[Test]
    public function it_tests_aws_rekognition_search_users_by_image_request()
    {
        /* SETUP */
        $externalCollectionId = 'test_collection_id';
        $image = UploadedFile::fake()->image('test_image.jpg');
        $imageData = new ImageData(
            bytes: base64_encode($image->getContent())
        );
        $maxUsers = 10;
        $methodName = 'searchUsersByImage';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->awsRekognitionService->searchUsersByImage($externalCollectionId, $imageData, $maxUsers);

        /* ASSERT */
        $this->metaDataAssertions($response);
        $this->assertInstanceOf(
            SearchUsersByImageResultData::class,
            $response
        );
        $this->assertNotNull($response->faceModelVersion);
        $this->assertNotNull($response->userMatches);
        $this->assertInstanceOf(DataCollection::class, $response->userMatches);
        $this->assertNotNull($response->searchedFace);
        $this->assertInstanceOf(
            SearchedFaceData::class,
            $response->searchedFace
        );
    }
}
