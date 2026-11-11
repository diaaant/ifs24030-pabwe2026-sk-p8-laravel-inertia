<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $like = '%'.mb_strtolower(trim((string) $search)).'%';

        return User::query()
            ->withCount('posts')
            ->when(trim((string) $search) !== '', fn ($q) => $q->where(
                fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$like])
            ))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Admin tidak boleh menghapus dirinya sendiri. */
    public function delete(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return false;
        }

        $target->delete();

        return true;
    }
}
