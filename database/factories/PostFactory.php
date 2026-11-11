<?php

namespace Database\Factories;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(5), '.');

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'category' => fake()->randomElement(PostCategory::cases())->value,
            'excerpt' => fake()->sentence(14),
            'content' => "## ".fake()->sentence(4)."\n\n".fake()->paragraph(5)."\n\n".fake()->paragraph(4),
            'is_published' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true, 'published_at' => now()->subDays(fake()->numberBetween(0, 30))]);
    }
}
