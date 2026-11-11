<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Judul Baru', 'category' => 'Teknologi', 'excerpt' => 'Ringkas.',
            'content' => '# Isi', 'is_published' => false,
        ], $override);
    }

    public function test_index_lists_only_own_posts_with_stats(): void
    {
        $user = User::factory()->create();
        Post::factory()->published()->count(2)->create(['user_id' => $user->id]);
        Post::factory()->create(['user_id' => $user->id, 'title' => 'Draf Saya']);
        Post::factory()->published()->create();

        $this->actingAs($user)->get('/dashboard/posts?search=draf')->assertInertia(fn (Assert $p) => $p
            ->component('Post/Index')
            ->where('stats', ['total' => 3, 'published' => 2, 'draft' => 1])
            ->has('posts.data', 1));
    }

    public function test_store_creates_post_with_unique_slug_and_cover(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts', $this->payload(['cover' => UploadedFile::fake()->image('c.jpg')]))
            ->assertRedirect('/dashboard/posts');
        $this->actingAs($user)->post('/dashboard/posts', $this->payload(['is_published' => true]));

        $this->assertDatabaseHas('posts', ['slug' => 'judul-baru', 'is_published' => false]);
        $this->assertDatabaseHas('posts', ['slug' => 'judul-baru-2', 'is_published' => true]);
        Storage::disk('public')->assertExists(Post::where('slug', 'judul-baru')->first()->cover_image_path);
    }

    public function test_store_validates_input(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts', ['title' => '', 'category' => 'Salah', 'content' => ''])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_slug_falls_back_when_title_has_no_letters(): void
    {
        $this->actingAs(User::factory()->create())->post('/dashboard/posts', $this->payload(['title' => '!!!']));

        $this->assertDatabaseHas('posts', ['slug' => 'artikel']);
    }

    public function test_show_own_post_and_forbid_others(): void
    {
        $user = User::factory()->create();
        $mine = Post::factory()->create(['user_id' => $user->id]);
        $other = Post::factory()->create();

        $this->actingAs($user)->get("/dashboard/posts/{$mine->id}")->assertInertia(fn (Assert $p) => $p->component('Post/Show'));
        $this->actingAs($user)->get("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->actingAs($user)->delete("/dashboard/posts/{$other->id}")->assertForbidden();
    }

    public function test_update_changes_status_and_published_at(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => true, 'cover' => UploadedFile::fake()->image('a.png')]))
            ->assertRedirect();
        $post->refresh();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->cover_image_path);

        $first = $post->published_at;
        $this->actingAs($user)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => true]));
        $this->assertTrue($post->refresh()->published_at->equalTo($first));

        $this->actingAs($user)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => false]));
        $this->assertNull($post->refresh()->published_at);
    }

    public function test_toggle_publish_both_directions(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success');
        $this->assertTrue($post->refresh()->is_published);

        $this->actingAs($user)->patch("/dashboard/posts/{$post->id}/publish");
        $this->assertFalse($post->refresh()->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_cover_upload_replace_and_remove(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('1.jpg')]);
        $old = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($old);
        $this->assertNotNull($post->cover_url);

        $this->actingAs($user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('2.jpg')]);
        Storage::disk('public')->assertMissing($old);

        $this->actingAs($user)->delete("/dashboard/posts/{$post->id}/cover");
        $this->assertNull($post->refresh()->cover_image_path);

        $this->actingAs($user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => 'bukan-gambar'])->assertSessionHasErrors('cover');
    }

    public function test_destroy_deletes_post_and_cover(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('1.jpg')]);
        $path = $post->refresh()->cover_image_path;

        $this->actingAs($user)->delete("/dashboard/posts/{$post->id}")->assertRedirect('/dashboard/posts');

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_cannot_use_member_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/posts')->assertForbidden();
    }
}
