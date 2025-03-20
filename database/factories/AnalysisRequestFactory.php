<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AnalysisOperation;
use App\Models\AnalysisRequest;
use App\Models\AwsCollection;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisRequest>
 */
final class AnalysisRequestFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AnalysisRequest>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AnalysisRequest>
     */
    protected $model = AnalysisRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();

        return [
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'operation' => AnalysisOperation::SEARCH_USERS_BY_IMAGE->value,
        ];
    }
}
