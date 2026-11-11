<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'role' => 'admin', 'password' => 'admin']
        );

        $member = User::updateOrCreate(
            ['username' => 'member'],
            ['name' => 'Penulis Contoh', 'email' => 'member@example.com', 'role' => 'member', 'password' => 'member']
        );

        if (Post::count() === 0) {
            Post::factory()->count(8)->published()->create(['user_id' => $member->id]);
            Post::factory()->count(3)->create(['user_id' => $member->id]);
            Post::factory()->count(2)->published()->create(['user_id' => $admin->id]);
        }
    }
}
