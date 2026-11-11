<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_hero_and_published_posts_only(): void
    {
        Post::factory()->published()->create(['title' => 'Terbit']);
        Post::factory()->create(['title' => 'Draf']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostIndex')
            ->where('hero', true)
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Terbit'));
    }

    public function test_articles_search_and_category_filter(): void
    {
        Post::factory()->published()->create(['title' => 'Belajar Laravel', 'category' => 'Teknologi']);
        Post::factory()->published()->create(['title' => 'Resep Sehat', 'category' => 'Gaya Hidup']);

        $this->get('/articles?search=laravel')->assertInertia(fn (Assert $p) => $p->where('hero', false)->has('posts.data', 1));
        $this->get('/articles?category=Gaya Hidup')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Resep Sehat'));
        $this->get('/articles?category=Tidak Ada')->assertInertia(fn (Assert $p) => $p->has('posts.data', 2));
    }

    public function test_show_published_article_with_related(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'halo-dunia', 'category' => 'Opini']);
        Post::factory()->published()->create(['category' => 'Opini']);

        $this->get('/articles/halo-dunia')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Public/PostShow')
            ->where('post.id', $post->id)
            ->has('related', 1));
    }

    public function test_draft_or_missing_article_returns_404(): void
    {
        Post::factory()->create(['slug' => 'draf']);

        $this->get('/articles/draf')->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }
}
