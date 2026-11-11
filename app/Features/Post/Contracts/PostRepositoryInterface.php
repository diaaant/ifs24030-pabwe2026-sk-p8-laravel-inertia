<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Post;

    public function relatedPublished(Post $post, int $limit = 3): Collection;

    public function paginateByUser(User $user, ?string $search, int $perPage = 10): LengthAwarePaginator;

    /** @return array{total: int, published: int, draft: int} */
    public function statsByUser(User $user): array;

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function getForExport(?User $user = null): Collection;

    public function slugExists(string $slug): bool;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;
}
