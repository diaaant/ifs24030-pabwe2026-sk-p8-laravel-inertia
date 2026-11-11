<?php

namespace App\Features\Post\Policies;

use App\Features\Post\Models\Post;
use App\Models\User;

class PostPolicy
{
    /** Penulis hanya boleh mengelola artikel miliknya sendiri. */
    public function manage(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
