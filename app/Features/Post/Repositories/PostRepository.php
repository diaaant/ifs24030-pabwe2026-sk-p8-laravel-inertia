<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        $enum = $category ? PostCategory::tryFrom($category) : null;

        return Post::query()
            ->with('user:id,name,username')
            ->published()
            ->search($search)
            ->when($enum, fn ($q) => $q->where('category', $enum->value))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with('user:id,name,username')
            ->published()
            ->where('slug', $slug)
            ->first();
    }

    public function relatedPublished(Post $post, int $limit = 3): Collection
    {
        return Post::query()
            ->with('user:id,name,username')
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category->value)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function paginateByUser(User $user, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->where('user_id', $user->id)
            ->search($search)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function statsByUser(User $user): array
    {
        $total = Post::where('user_id', $user->id)->count();
        $published = Post::where('user_id', $user->id)->published()->count();

        return ['total' => $total, 'published' => $published, 'draft' => $total - $published];
    }

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name,username')
            ->search($search)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getForExport(?User $user = null): Collection
    {
        return Post::query()
            ->with('user:id,name,username')
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('id')
            ->get();
    }

    public function slugExists(string $slug): bool
    {
        return Post::where('slug', $slug)->exists();
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
