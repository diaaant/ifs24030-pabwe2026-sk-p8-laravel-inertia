<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Admin/PostList', [
            'posts' => $this->posts->listAll($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function export(): StreamedResponse
    {
        return $this->excel->download(
            $this->posts->forExport(),
            'semua-artikel-'.now()->format('Ymd_His').'.xlsx',
            withAuthor: true
        );
    }
}
