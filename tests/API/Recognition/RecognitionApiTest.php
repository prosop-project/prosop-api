<?php

namespace Tests\API\Recognition;

use App\Models\AwsCollection;
use App\Models\User;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\TestSupport\MockRekognitionTrait;

class RecognitionApiTest extends TestCase
{
    use MockRekognitionTrait;

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
    public function it_tests_create_aws_collection_request()
    {
        /* SETUP */
        $parameters = [
            'collection_id' => 'test_collection_id_0',
        ];
        $faceModelVersion = '7.0';
        $externalCollectionArn = 'aws:rekognition:us-east-1:605134457385:collection/' . $parameters['collection_id'];
        $methodName = 'createCollection';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('recognition.create.collection', $parameters));

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'external_collection_id' => $parameters['collection_id'],
                    'external_collection_arn' => $externalCollectionArn,
                    'face_model_version' => $faceModelVersion,
                ]
            ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsCollection_model_activity',
            'description' => 'AwsCollection is created!',
            'subject_id' => AwsCollection::query()->first()->id,
            'subject_type' => AwsCollection::class,
            'event' => 'created',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->external_collection_id' => $parameters['collection_id'],
            'properties->attributes->external_collection_arn' => $externalCollectionArn,
            'properties->attributes->face_model_version' => $faceModelVersion,
        ]);
    }

    #[Test]
    public function it_tests_get_aws_collections_request()
    {
        /* SETUP */
        AwsCollection::factory()->create(['external_collection_id' => 'test_collection_id_0']);
        AwsCollection::factory()->create(['external_collection_id' => 'test_collection_id_1']);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('recognition.aws.collections'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'external_collection_id' => 'test_collection_id_0',
                        'face_model_version' => '7.0',
                    ],
                    [
                        'external_collection_id' => 'test_collection_id_1',
                        'face_model_version' => '7.0',
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_delete_aws_collection_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $methodName = 'deleteCollection';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('recognition.delete.collection', ['collection' => $awsCollection->id]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Aws collection is deleted from both database and aws side successfully!',
                ],
            ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsCollection_model_activity',
            'description' => 'AwsCollection is deleted!',
            'subject_id' => $awsCollection->id,
            'subject_type' => AwsCollection::class,
            'event' => 'deleted',
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->external_collection_id' => $awsCollection->external_collection_id,
            'properties->old->external_collection_arn' => $awsCollection->external_collection_arn,
            'properties->old->face_model_version' => $awsCollection->face_model_version,
            ]);
    }
}
