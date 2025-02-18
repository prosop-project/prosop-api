<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Database\Factories\Concerns\RefreshOnCreateTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    /**
     * @use RefreshOnCreateTrait<Subscription>
     */
    use RefreshOnCreateTrait;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Subscription>
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' =>  User::factory(),
            'subscriber_id' => User::factory(),
        ];
    }
}
