<?php

namespace Tests\API\Analysis;

use App\Enums\ActivityEvent;
use App\Enums\ActivityLogName;
use App\Enums\AnalysisOperationName;
use App\Models\AnalysisOperation;
use App\Models\AwsCollection;
use App\Models\AwsSimilarityResult;
use App\Models\AwsUser;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Spatie\DeletedModels\Models\DeletedModel;
use Tests\TestCase;

class AnalysisApiTest extends TestCase
{
    #[Test]
    public function it_tests_get_user_analysis_operations_route()
    {
        /* SETUP */
        $user = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        AnalysisOperation::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $params = [
            'public_uuid' => $user->public_uuid,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('analysis.operations.user', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $user->id,
                        'aws_collection_id' => $awsCollection->id,
                        'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_get_user_analysis_operations_when_wrong_operation_set_in_query_param_route()
    {
        /* SETUP */
        $user = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        AnalysisOperation::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $params = [
            'public_uuid' => $user->public_uuid,
            'operation' => 'wrong_operation',
        ];

        /* EXECUTE */
        $response = $this->getJson(route('analysis.operations.user', $params));

        /* ASSERT */
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['operation']);
    }

    #[Test]
    public function it_tests_get_user_analysis_operations_with_aws_similarity_results_route()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $userWhoRequestedAnalysis->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $firstUser = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $firstUser->id,
        ]);
        $firstSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
        ]);
        $secondUser = User::factory()->create();
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $secondUser->id,
        ]);
        $secondSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('analysis.operations.user', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $userWhoRequestedAnalysis->id,
                        'aws_collection_id' => $awsCollection->id,
                        'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
                        'aws_similarity_results' => [
                            [
                                'analysis_operation_id' => $analysisOperation->id,
                                'similarity' => $firstSimilarityResult->similarity,
                                'aws_user' => [
                                    'user_id' => $firstUser->id,
                                    'user' => [
                                        'id' => $firstUser->id,
                                        'name' => $firstUser->name,
                                        'username' => $firstUser->username,
                                        'avatar' => $firstUser->avatar,
                                    ],
                                ],
                            ],
                            [
                                'analysis_operation_id' => $analysisOperation->id,
                                'similarity' => $secondSimilarityResult->similarity,
                                'aws_user' => [
                                    'user_id' => $secondUser->id,
                                    'user' => [
                                        'id' => $secondUser->id,
                                        'name' => $secondUser->name,
                                        'username' => $secondUser->username,
                                        'avatar' => $secondUser->avatar,
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_get_user_analysis_operations_with_query_param_filters_route()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $userWhoRequestedAnalysis->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $firstUser = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $firstUser->id,
        ]);
        $firstSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
        ]);
        $secondUser = User::factory()->create();
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $secondUser->id,
        ]);
        $secondSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ];

        /* EXECUTE */
        $response = $this->getJson(route('analysis.operations.user', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $userWhoRequestedAnalysis->id,
                        'aws_collection_id' => $awsCollection->id,
                        'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
                        'aws_similarity_results' => [
                            [
                                'analysis_operation_id' => $analysisOperation->id,
                                'similarity' => $firstSimilarityResult->similarity,
                                'aws_user' => [
                                    'user_id' => $firstUser->id,
                                    'user' => [
                                        'id' => $firstUser->id,
                                        'name' => $firstUser->name,
                                        'username' => $firstUser->username,
                                        'avatar' => $firstUser->avatar,
                                    ],
                                ],
                            ],
                            [
                                'analysis_operation_id' => $analysisOperation->id,
                                'similarity' => $secondSimilarityResult->similarity,
                                'aws_user' => [
                                    'user_id' => $secondUser->id,
                                    'user' => [
                                        'id' => $secondUser->id,
                                        'name' => $secondUser->name,
                                        'username' => $secondUser->username,
                                        'avatar' => $secondUser->avatar,
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ]);
    }

    #[Test]
    public function it_tests_delete_analysis_operation_route()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $userWhoRequestedAnalysis->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $firstUser = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $firstUser->id,
        ]);
        $firstAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
        ]);
        $secondUser = User::factory()->create();
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $secondUser->id,
        ]);
        $secondAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'analysis_operation_id' => $analysisOperation->id,
        ];

        /* EXECUTE */
        $response = $this->deleteJson(route('analysis.delete.operation', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Analysis operation is deleted successfully!',
                ]
            ]);
        $this->assertDatabaseMissing('analysis_operations', [
            'id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseMissing('aws_similarity_results', [
            'analysis_operation_id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::ANALYSIS_OPERATION_MODEL_ACTIVITY->value,
            'description' => 'AnalysisOperation is deleted!',
            'subject_type' => AnalysisOperation::class,
            'subject_id' => $analysisOperation->id,
            'event' => ActivityEvent::DELETED->value,
            'properties->old->id' => $analysisOperation->id,
            'properties->old->aws_collection_id' => $awsCollection->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $firstAwsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
            'properties->old->id' => $firstAwsSimilarityResult->id,
            'properties->old->analysis_operation_id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $secondAwsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
            'properties->old->id' => $secondAwsSimilarityResult->id,
            'properties->old->analysis_operation_id' => $analysisOperation->id,
        ]);
    }

    #[Test]
    public function it_tests_delete_analysis_operation_route_if_deleted_models_are_saved_in_deleted_models_table()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $userWhoRequestedAnalysis->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $firstUser = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $firstUser->id,
        ]);
        $firstAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
        ]);
        $secondUser = User::factory()->create();
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $secondUser->id,
        ]);
        $secondAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'analysis_operation_id' => $analysisOperation->id,
        ];
        $this->deleteJson(route('analysis.delete.operation', $params));

        /* EXECUTE */
        $deletedAnalysisOperation = DeletedModel::query()
            ->where('key', $analysisOperation->id)
            ->where('model', AnalysisOperation::class)
            ->first();
        $firstDeletedAwsSimilarityResult = DeletedModel::query()
            ->where('key', $firstAwsSimilarityResult->id)
            ->where('model', AwsSimilarityResult::class)
            ->first();
        $secondDeletedAwsSimilarityResult = DeletedModel::query()
            ->where('key', $secondAwsSimilarityResult->id)
            ->where('model', AwsSimilarityResult::class)
            ->first();

        /* ASSERT */
        $this->assertNotNull($deletedAnalysisOperation);
        $this->assertNotNull($firstDeletedAwsSimilarityResult);
        $this->assertNotNull($secondDeletedAwsSimilarityResult);
        $this->assertDatabaseMissing('analysis_operations', [
            'id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseMissing('aws_similarity_results', [
            'analysis_operation_id' => $analysisOperation->id,
        ]);
    }

    #[Test]
    public function it_tests_delete_aws_similarity_result_route()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $userWhoRequestedAnalysis->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $user = User::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
        ]);
        $awsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $awsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'analysis_operation_id' => $analysisOperation->id,
            'aws_similarity_result_id' => $awsSimilarityResult->id,
        ];

        /* EXECUTE */
        $response = $this->deleteJson(route('analysis.delete.aws.similarity.result', $params));

        /* ASSERT */
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'message' => 'Aws similarity result is deleted successfully!',
                ]
            ]);
        $this->assertDatabaseMissing('aws_similarity_results', [
            'id' => $awsSimilarityResult->id,
            'analysis_operation_id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => ActivityLogName::AWS_SIMILARITY_RESULT_MODEL_ACTIVITY->value,
            'description' => 'AwsSimilarityResult is deleted!',
            'subject_type' => AwsSimilarityResult::class,
            'subject_id' => $awsSimilarityResult->id,
            'event' => ActivityEvent::DELETED->value,
            'properties->old->id' => $awsSimilarityResult->id,
            'properties->old->analysis_operation_id' => $analysisOperation->id,
        ]);
    }

    #[Test]
    public function it_tests_delete_aws_similarity_result_route_form_request_authorize()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $notOriginalUser = User::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $notOriginalUser->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $user = User::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
        ]);
        $awsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $awsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'analysis_operation_id' => $analysisOperation->id,
            'aws_similarity_result_id' => $awsSimilarityResult->id,
        ];

        /* EXECUTE */
        $response = $this->deleteJson(route('analysis.delete.aws.similarity.result', $params));

        /* ASSERT */
        $response->assertForbidden()
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
        $this->assertDatabaseHas('aws_similarity_results', [
            'id' => $awsSimilarityResult->id,
            'analysis_operation_id' => $analysisOperation->id,
        ]);
    }

    #[Test]
    public function it_tests_delete_analysis_operation_route_form_request_authorize()
    {
        /* SETUP */
        $userWhoRequestedAnalysis = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $notOriginalUser = User::factory()->create();
        $analysisOperation = AnalysisOperation::factory()->create([
            'user_id' => $notOriginalUser->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ]);
        $firstUser = User::factory()->create();
        $firstAwsUser = AwsUser::factory()->create([
            'user_id' => $firstUser->id,
        ]);
        $firstAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $firstAwsUser->id,
        ]);
        $secondUser = User::factory()->create();
        $secondAwsUser = AwsUser::factory()->create([
            'user_id' => $secondUser->id,
        ]);
        $secondAwsSimilarityResult = AwsSimilarityResult::factory()->create([
            'analysis_operation_id' => $analysisOperation->id,
            'aws_user_id' => $secondAwsUser->id,
        ]);
        $params = [
            'public_uuid' => $userWhoRequestedAnalysis->public_uuid,
            'analysis_operation_id' => $analysisOperation->id,
        ];

        /* EXECUTE */
        $response = $this->deleteJson(route('analysis.delete.operation', $params));

        /* ASSERT */
        $response->assertForbidden()
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
        $this->assertDatabaseHas('analysis_operations', [
            'id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseHas('aws_similarity_results', [
            'id' => $firstAwsSimilarityResult->id,
            'analysis_operation_id' => $analysisOperation->id,
        ]);
        $this->assertDatabaseHas('aws_similarity_results', [
            'id' => $secondAwsSimilarityResult->id,
            'analysis_operation_id' => $analysisOperation->id,
        ]);
    }
}
