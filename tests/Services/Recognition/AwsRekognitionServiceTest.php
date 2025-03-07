<?php

namespace Tests\Services\Recognition;

use App\Services\Recognition\AwsRekognitionService;
use Aws\Rekognition\RekognitionClient;
use Mockery\MockInterface;
use MoeMizrak\Rekognition\Data\ResultData\DeleteCollectionResultData;
use MoeMizrak\Rekognition\Data\ResultData\ListCollectionsResultData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\TestSupport\MockRekognitionTrait;


class AwsRekognitionServiceTest extends TestCase
{
    use MockRekognitionTrait;

    private AwsRekognitionService $awsRekognitionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->awsRekognitionService =app(AwsRekognitionService::class);
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

    /**
     * Mock the Rekognition client for testing in order to avoid making real requests.
     *
     * @param string $methodName
     *
     * @return void
     */
    private function mockRekognitionClient(string $methodName): void
    {
        $mockResponse = $this->mockRekognitionResponse($methodName);

        $this->mock(RekognitionClient::class, function (MockInterface $mock) use($methodName, $mockResponse) {
            $mock->shouldReceive($methodName)
                ->once()
                ->andReturn($mockResponse);
        });
    }

    #[Test]
    public function it_tests_aws_rekognition_create_collection_request()
    {
        /* SETUP */
        $collectionId = 'test_collection_id_0';
        $validatedRequest = [
            'collection_id' => $collectionId,
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
}
