<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Follower;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Follower>
 */
final class FollowerFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<Follower>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Follower>
     */
    protected $model = Follower::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' =>  User::factory(),
            'follower_id' => User::factory(),
        ];
    }
}
