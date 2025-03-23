<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AnalysisOperationName;
use App\Models\AnalysisOperation;
use App\Models\AwsCollection;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalysisOperation>
 */
final class AnalysisOperationFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AnalysisOperation>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AnalysisOperation>
     */
    protected $model = AnalysisOperation::class;

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
            'operation' => AnalysisOperationName::SEARCH_USERS_BY_IMAGE->value,
        ];
    }
}
