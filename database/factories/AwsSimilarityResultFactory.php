<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalysisOperation;
use App\Models\AwsSimilarityResult;
use App\Models\AwsUser;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AwsSimilarityResult>
 */
final class AwsSimilarityResultFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AwsSimilarityResult>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AwsSimilarityResult>
     */
    protected $model = AwsSimilarityResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $analysisOperation = AnalysisOperation::factory()->create();
        $awsUser = AwsUser::factory()->create();

        return [
            'analysis_operation_id' => $analysisOperation->id,
            'similarity' => fake()->randomFloat(2, 0, 100),
            'aws_user_id' => $awsUser->id,
        ];
    }
}
