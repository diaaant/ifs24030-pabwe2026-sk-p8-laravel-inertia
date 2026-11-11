<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_renders_and_shares_auth_user(): void
    {
        // Cek guest dulu (actingAs mempersist state auth untuk request berikutnya).
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('auth.user', null));

        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertInertia(fn (Assert $p) => $p
            ->component('Profile/Edit')->where('auth.user.id', $user->id)->where('auth.user.role', 'member'));
    }

    public function test_update_profile_password_and_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['username' => 'lama']);

        $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru', 'username' => 'baru', 'email' => 'baru@example.com',
            'password' => 'passwordbaru', 'password_confirmation' => 'passwordbaru',
            'photo' => $this->fakeImage('p.jpg'),
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('baru', $user->username);
        $this->assertTrue(Hash::check('passwordbaru', $user->password));
        $this->assertNotNull($user->profile_photo_url);
        $old = $user->profile_photo_path;

        $this->actingAs($user)->put('/profile', ['name' => 'N', 'username' => 'baru', 'photo' => $this->fakeImage('q.jpg')]);
        Storage::disk('public')->assertMissing($old);
        $this->assertTrue(Hash::check('passwordbaru', $user->refresh()->password));
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'dipakai']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => 'dipakai'])->assertSessionHasErrors('username');
    }
}