<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pillars = ['offer', 'traffic', 'conversion', 'delivery', 'continuity'];

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, asText: true),
            'pillar' => fake()->randomElement($pillars),
            'topic_id' => null,
            'is_cot' => false,
            'cot_at' => null,
            'cot_by' => null,
            'is_pinned' => false,
            'is_signal' => false,
            'rune_active' => false,
            'rune_expires_at' => null,
            'rune_first_comment_user_id' => null,
            'view_count' => 0,
        ];
    }

    /**
     * State for signal post (short)
     */
    public function signal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_signal' => true,
            'content' => fake()->sentence(20),
        ]);
    }

    /**
     * State for CỐT (curated essential) post
     */
    public function cot(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cot' => true,
            'cot_at' => now(),
            'cot_by' => User::factory(),
        ]);
    }

    /**
     * State for post with active rune
     */
    public function withRune(): static
    {
        return $this->state(fn (array $attributes) => [
            'rune_active' => true,
            'rune_expires_at' => now()->addHours(24),
        ]);
    }
}
