<?php

namespace Tests\Unit;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_relations_scopes_and_accessors(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->published()->create(['user_id' => $user->id, 'cover_image_path' => 'covers/a.jpg', 'title' => 'Halo Dunia']);
        Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($post->user->is($user));
        $this->assertCount(2, $user->posts);
        $this->assertCount(1, Post::published()->get());
        $this->assertCount(1, Post::search('halo')->get());
        $this->assertCount(2, Post::search('   ')->get());
        $this->assertStringContainsString('covers/a.jpg', $post->cover_url);
        $this->assertNull(Post::where('id', '!=', $post->id)->first()->cover_url);
        $this->assertTrue($user->posts()->first()->category instanceof \App\Features\Post\Enums\PostCategory);
    }

    public function test_user_role_helpers(): void
    {
        $this->assertTrue(User::factory()->admin()->make()->isAdmin());
        $this->assertFalse(User::factory()->make()->isAdmin());
        $this->assertNull(User::factory()->make()->profile_photo_url);
    }
}
