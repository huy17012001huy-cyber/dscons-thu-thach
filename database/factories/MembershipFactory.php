<?php

namespace Database\Factories;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(7),
            'starts_at' => now(),
            'expires_at' => null,
            'paid_amount' => 0,
            'referred_by' => null,
        ];
    }

    /**
     * State for active membership
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'trial_ends_at' => null,
            'expires_at' => now()->addMonths(1),
            'paid_amount' => 10,
        ]);
    }

    /**
     * State for expired membership
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'trial_ends_at' => now()->subDays(1),
            'expires_at' => now()->subDays(1),
        ]);
    }

    /**
     * State for banned membership
     */
    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'banned',
            'trial_ends_at' => null,
            'expires_at' => null,
        ]);
    }

    /**
     * State for trial membership that is expired
     */
    public function expiredTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
            'trial_ends_at' => now()->subDay(),
            'expires_at' => null,
        ]);
    }
}
