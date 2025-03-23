<?php

namespace Tests\API\Recognition;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Jobs\IndexFacesJob;
use App\Jobs\SearchUsersByImageJob;
use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecognitionApiTest extends TestCase
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
    public function it_tests_create_aws_collection_request()
    {
        /* SETUP */
        $parameters = [
            'external_collection_id' => 'test_collection_id_0',
        ];
        $faceModelVersion = '7.0';
        $externalCollectionArn = 'aws:rekognition:us-east-1:605134457385:collection/' . $parameters['external_collection_id'];
        $methodName = 'createCollection';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('recognition.create.collection'), $parameters);

        /* ASSERT */
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'external_collection_id' => $parameters['external_collection_id'],
                    'external_collection_arn' => $externalCollectionArn,
                    'face_model_version' => $faceModelVersion,
                ]
            ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'AwsCollection_model_activity',
            'description' => 'AwsCollection is created!',
            'subject_id' => AwsCollection::query()->first()->id,
            'subject_type' => AwsCollection::class,
            'event' => ActivityEvent::CREATED->value,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->external_collection_id' => $parameters['external_collection_id'],
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
        ])->deleteJson(route('recognition.delete.collection', ['awsCollection' => $awsCollection->id]));

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
            'event' => ActivityEvent::DELETED->value,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->external_collection_id' => $awsCollection->external_collection_id,
            'properties->old->external_collection_arn' => $awsCollection->external_collection_arn,
            'properties->old->face_model_version' => $awsCollection->face_model_version,
            ]);
    }

    #[Test]
    public function it_tests_create_aws_user_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $parameters = [
            'aws_collection_id' => $awsCollection->id,
            'user_id' => $this->user->id,
        ];
        $methodName = 'createUser';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('recognition.create.aws.user'), $parameters);

        /* ASSERT */
        $externalUserId = generate_external_id($parameters['user_id']);
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user_id' => $parameters['user_id'],
                    'aws_collection_id' => $parameters['aws_collection_id'],
                    'external_user_id' => $externalUserId,
                ]
            ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is created!',
            'subject_id' => AwsUser::query()->first()->id,
            'subject_type' => AwsUser::class,
            'event' => ActivityEvent::CREATED->value,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->attributes->user_id' => $parameters['user_id'],
            'properties->attributes->aws_collection_id' => $parameters['aws_collection_id'],
            'properties->attributes->external_user_id' => $externalUserId,
        ]);
    }

    #[Test]
    public function it_tests_delete_aws_user_request()
    {
        /* SETUP */
        $externalUserId = generate_external_id($this->user->id);
        $awsCollection = AwsCollection::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'user_id' => $this->user->id,
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => $externalUserId,
        ]);
        $parameters = [
            'aws_collection_id' => $awsCollection->id,
            'user_id' => $this->user->id,
        ];
        $methodName = 'deleteUser';
        $this->mockRekognitionClient($methodName);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('recognition.delete.aws.user', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Aws user is deleted from both database and aws side successfully!',
                ],
            ]);
        $this->assertDatabaseMissing('aws_users', [
            'id' => $awsUser->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_USER_MODEL_ACTIVITY->value,
            'description' => 'AwsUser is deleted!',
            'subject_id' => $awsUser->id,
            'subject_type' => AwsUser::class,
            'event' => ActivityEvent::DELETED->value,
            'causer_id' => $this->user->id,
            'causer_type' => User::class,
            'properties->old->user_id' => $parameters['user_id'],
            'properties->old->aws_collection_id' => $parameters['aws_collection_id'],
            'properties->old->external_user_id' => $externalUserId,
        ]);
    }

    #[Test]
    public function it_tests_get_aws_users_request()
    {
        /* SETUP */
        $awsCollection = AwsCollection::factory()->create();
        $firstUser = User::factory()->create();
        $externalUserId = generate_external_id($firstUser->id);
        AwsUser::factory()->create([
            'user_id' => $firstUser->id,
            'external_user_id' => $externalUserId,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondUser = User::factory()->create();
        $secondExternalUserId = generate_external_id($secondUser->id);
        AwsUser::factory()->create([
            'user_id' => $secondUser->id,
            'external_user_id' => $secondExternalUserId,
            'aws_collection_id' => $awsCollection->id,
        ]);

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('recognition.aws.users'));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $firstUser->id,
                        'aws_collection_id' => $awsCollection->id,
                        'external_user_id' => $externalUserId,
                    ],
                    [
                        'user_id' => $secondUser->id,
                        'aws_collection_id' => $awsCollection->id,
                        'external_user_id' => $secondExternalUserId,
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_process_faces_request()
    {
        /* SETUP */
        Queue::fake();
        $awsCollection = AwsCollection::factory()->create();
        $fakeImages = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.png'),
        ];
        $parameters = [
            'aws_collection_id' => $awsCollection->id,
            'images' => $fakeImages,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('recognition.process.faces', ['user' => $this->user->id]), $parameters);

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Process faces request is sent successfully!',
                ]
            ]);
        // Assert IndexFacesJob is dispatched
        Queue::assertPushed(IndexFacesJob::class);
    }

    #[Test]
    public function it_tests_get_aws_faces_request()
    {
        /* SETUP */
        $firstAwsFace = AwsFace::factory()->create();
        $secondAwsFace = AwsFace::factory()->create();

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('recognition.aws.faces', ['user' => $this->user->id]));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $firstAwsFace->id,
                        'user_id' => $firstAwsFace->user_id,
                        'external_face_id' => $firstAwsFace->external_face_id,
                    ],
                    [
                        'id' => $secondAwsFace->id,
                        'user_id' => $secondAwsFace->user_id,
                        'external_face_id' => $secondAwsFace->external_face_id,
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_list_external_faces_request()
    {
        /* SETUP */
        $methodName = 'listFaces';
        $this->mockRekognitionClient($methodName);
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $firstAwsFace = AwsFace::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsFace = AwsFace::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
        ]);
        $parameters = [
            'aws_collection_id' => $awsCollection->id,
            'user_id' => $user->id,
            'aws_face_ids' => [$firstAwsFace->id, $secondAwsFace->id],
            'max_results' => 20,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson(route('recognition.external.list.faces', $parameters));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'faces' => [
                        [
                            'external_user_id' => 'test_user_id',
                            'external_face_id' => '038388f6-221a-4f3f-aab5-1ccd8256f7e8',
                        ],
                        [
                            'external_user_id' => 'test_user_id',
                            'external_face_id' => '938388f6-221a-4f3f-aab5-1ccd8256f7e3',
                        ],
                    ],
                ],
            ]);
    }

    #[Test]
    public function it_tests_delete_faces_request()
    {
        /* SETUP */
        $methodName = 'deleteFaces';
        $this->mockRekognitionClient($methodName);
        $awsCollection = AwsCollection::factory()->create();
        $firstAwsFace = AwsFace::factory()->create([
            'aws_collection_id' => $awsCollection->id,
        ]);
        $secondAwsFace = AwsFace::factory()->create([
            'aws_collection_id' => $awsCollection->id,
        ]);
        $params = [
            'aws_collection_id' => $awsCollection->id,
            'aws_face_ids' => [$firstAwsFace->id, $secondAwsFace->id],
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson(route('recognition.delete.faces', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Faces are deleted from both database and aws side successfully!',
                ],
            ]);
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

    #[Test]
    public function it_tests_search_users_by_image_request()
    {
        /* SETUP */
        Queue::fake();
        $awsCollection = AwsCollection::factory()->create();
        $user = User::factory()->create();
        $parameters = [
            'analysis_operations' => ['search_users_by_image'],
            'aws_collection_id' => $awsCollection->id,
            'image' => UploadedFile::fake()->image('test.jpg'),
            'max_users' => 5,
            'public_uuid' => $user->public_uuid,
        ];

        /* EXECUTE */
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson(route('recognition.search.collection'), $parameters);

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Search collection request is sent successfully!',
                ]
            ]);
        // Assert SearchUsersByImageJob is dispatched
        Queue::assertPushed(SearchUsersByImageJob::class);
    }
}
