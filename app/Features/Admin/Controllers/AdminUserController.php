<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $users,
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Admin/UserList', [
            'users' => $this->users->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (! $this->users->delete($request->user(), $user)) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    public function posts(Request $request, User $user): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Admin/UserPostList', [
            'owner' => $user->only(['id', 'name', 'username']),
            'posts' => $this->posts->listForUser($user, $search),
            'stats' => $this->posts->statsForUser($user),
            'filters' => ['search' => $search],
        ]);
    }

    public function exportPosts(User $user): StreamedResponse
    {
        return $this->excel->download(
            $this->posts->forExport($user),
            'artikel-'.$user->username.'-'.now()->format('Ymd_His').'.xlsx',
            withAuthor: true
        );
    }
}
