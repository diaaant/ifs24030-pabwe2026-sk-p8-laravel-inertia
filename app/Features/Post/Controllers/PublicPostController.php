<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function __construct(private readonly PostService $posts) {}

    public function home(Request $request): Response
    {
        return $this->render($request, hero: true);
    }

    public function index(Request $request): Response
    {
        return $this->render($request, hero: false);
    }

    public function show(string $slug): Response
    {
        $post = $this->posts->findPublished($slug);
        abort_if($post === null, 404);

        return Inertia::render('Public/PostShow', [
            'post' => $post,
            'related' => $this->posts->related($post),
        ]);
    }

    private function render(Request $request, bool $hero): Response
    {
        $search = $request->string('search')->toString();
        $category = $request->string('category')->toString();

        return Inertia::render('Public/PostIndex', [
            'hero' => $hero,
            'posts' => $this->posts->listPublished($search, $category),
            'filters' => ['search' => $search, 'category' => $category],
            'categories' => PostCategory::options(),
        ]);
    }
}
