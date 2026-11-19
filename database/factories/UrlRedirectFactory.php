<?php

namespace Database\Factories;

use App\Models\UrlRedirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UrlRedirect>
 */
class UrlRedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_path' => '/old-'.fake()->unique()->slug(),
            'destination' => '/',
            'status_code' => 301,
            'is_active' => true,
            'history' => [],
        ];
    }
}
