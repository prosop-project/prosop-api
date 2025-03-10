<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AwsCollection;
use App\Models\AwsFace;
use App\Models\AwsUser;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AwsFace>
 */
final class AwsFaceFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AwsFace>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AwsFace>
     */
    protected $model = AwsFace::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $awsCollection = AwsCollection::factory()->create();
        $awsUser = AwsUser::factory()->create([
            'user_id' => $user->id,
            'aws_collection_id' => $awsCollection->id,
            'external_user_id' => config('aws-rekognition.reference_prefix') . '-' . $user->id,
        ]);

        return [
            'user_id' => $user->id,
            'aws_user_id' => $awsUser->id,
            'aws_collection_id' => $awsCollection->id,
            'external_face_id' => $this->faker->uuid,
            'confidence' => $this->faker->randomFloat(2, 0, 100),
            'external_image_id' => $this->faker->word(),
            'image_id' => $this->faker->uuid,
        ];
    }
}
