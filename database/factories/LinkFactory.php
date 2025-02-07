<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
final class LinkFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<Link>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Link>
     */
    protected $model = Link::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' =>  User::factory(),
            'type' => 'url',
            'description' => fake()->sentence,
            'value' => fake()->url,
            'is_visible' => true,
            'click_count' => fake()->numberBetween(0, 1000),
        ];
    }
}
