<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_member_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin/posts')->assertRedirect('/login');
    }

    public function test_user_list_with_search(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Citra Lestari', 'username' => 'citra']);
        Post::factory()->count(2)->create(['user_id' => User::where('username', 'citra')->first()->id]);

        $this->actingAs($admin)->get('/admin/users')->assertInertia(fn (Assert $p) => $p->component('Admin/UserList')->has('users.data', 2));
        $this->actingAs($admin)->get('/admin/users?search=CITRA')->assertInertia(fn (Assert $p) => $p
            ->has('users.data', 1)->where('users.data.0.posts_count', 2));
    }

    public function test_delete_user_but_not_self(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)->delete("/admin/users/{$target->id}")->assertSessionHas('success');
        $this->assertModelMissing($target);

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_view_and_export_user_posts(): void
    {
        $admin = $this->admin();
        $author = User::factory()->create(['username' => 'penulis']);
        Post::factory()->count(3)->create(['user_id' => $author->id]);

        $this->actingAs($admin)->get("/admin/users/{$author->id}/posts?search=")->assertInertia(fn (Assert $p) => $p
            ->component('Admin/UserPostList')->has('posts.data', 3)->where('stats.total', 3));

        $this->actingAs($admin)->get("/admin/users/{$author->id}/posts/export")->assertDownload();
    }

    public function test_monitor_and_export_all_posts(): void
    {
        $admin = $this->admin();
        Post::factory()->count(2)->create();

        $this->actingAs($admin)->get('/admin/posts')->assertInertia(fn (Assert $p) => $p->component('Admin/PostList')->has('posts.data', 2));

        $response = $this->actingAs($admin)->get('/admin/posts/export');
        $response->assertDownload();
        ob_start();
        $response->sendContent();
        ob_end_clean();
    }
}