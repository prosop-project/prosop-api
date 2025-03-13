<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AwsCollection;
use App\Models\AwsUser;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AwsUser>
 */
final class AwsUserFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<AwsUser>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AwsUser>
     */
    protected $model = AwsUser::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'aws_collection_id' => AwsCollection::factory(),
            'external_user_id' => generate_external_id($user->id),
        ];
    }
}
