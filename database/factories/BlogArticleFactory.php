<?php

namespace Database\Factories;

use App\Models\BlogArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogArticle>
 */
class BlogArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'slug' => fake()->unique()->slug(),
            'category' => fake()->randomElement(['Moving', 'Cleaning', 'Home care']),
            'excerpt' => fake()->paragraph(2),
            'body' => "## Planning your service\n\n".fake()->paragraphs(4, true),
            'seo_title' => null,
            'seo_description' => null,
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'published', 'published_at' => now()]);
    }
}
