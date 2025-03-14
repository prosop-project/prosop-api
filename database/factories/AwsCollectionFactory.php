<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AwsCollection;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AwsCollection>
 */
final class AwsCollectionFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AwsCollection>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AwsCollection>
     */
    protected $model = AwsCollection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $externalCollectionId = fake()->unique()->word;

        return [
            'external_collection_id' => $externalCollectionId,
            'external_collection_arn' => 'aws:rekognition:us-east-1:605134457385:collection/' . $externalCollectionId,
            'tags' => ['tag_key' => 'tag_value'],
            'face_model_version' => '7.0',
        ];
    }
}
