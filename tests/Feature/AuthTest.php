<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_render(): void
    {
        $this->get('/login')->assertInertia(fn (Assert $p) => $p->component('Auth/Login'));
        $this->get('/register')->assertInertia(fn (Assert $p) => $p->component('Auth/Register'));
    }

    public function test_member_login_redirects_to_dashboard(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'budi', 'password' => 'rahasia'])->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        User::factory()->admin()->create(['username' => 'root', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'root', 'password' => 'rahasia'])->assertRedirect('/admin/users');
    }

    public function test_login_fails_with_wrong_credentials_and_validates_input(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'budi', 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
        $this->assertGuest();
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Sari', 'username' => 'sari_01', 'password' => 'rahasia', 'password_confirmation' => 'rahasia',
        ])->assertRedirect('/dashboard/posts');

        $this->assertDatabaseHas('users', ['username' => 'sari_01', 'role' => 'member']);
        $this->assertAuthenticated();
    }

    public function test_register_rejects_duplicate_username(): void
    {
        User::factory()->create(['username' => 'sari']);

        $this->post('/register', [
            'name' => 'Sari', 'username' => 'sari', 'password' => 'rahasia', 'password_confirmation' => 'rahasia',
        ])->assertSessionHasErrors('username');
    }

    public function test_logout_clears_session(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_and_authenticated_user_skips_login(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect();
    }
}
