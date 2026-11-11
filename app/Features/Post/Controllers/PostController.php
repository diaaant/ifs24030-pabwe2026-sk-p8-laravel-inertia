<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
        private readonly AiClientInterface $ai,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Post/Index', [
            'posts' => $this->posts->listForUser($request->user(), $search),
            'stats' => $this->posts->statsForUser($request->user()),
            'filters' => ['search' => $search],
            'categories' => PostCategory::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $this->posts->create($request->user(), $data, $request->file('cover'));

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil disimpan.');
    }

    public function show(Post $post): Response
    {
        return Inertia::render('Post/Show', ['post' => $post, 'categories' => PostCategory::options()]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $this->posts->update($post, $data, $request->file('cover'));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->posts->delete($post);

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        $post = $this->posts->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function uploadCover(Request $request, Post $post): RedirectResponse
    {
        $request->validate(['cover' => ['required', 'image', 'max:2048']]);

        $this->posts->uploadCover($post, $request->file('cover'));

        return back()->with('success', 'Foto sampul diunggah.');
    }

    public function removeCover(Post $post): RedirectResponse
    {
        $this->posts->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        return $this->excel->download(
            $this->posts->forExport($user),
            'artikel-'.$user->username.'-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function import(Request $request): RedirectResponse
    {
        // Jangan pakai "mimes": aturan itu memeriksa MIME isi file, sehingga
        // .xlsx korup gagal validasi dan tidak sampai ke try/catch.
        $request->validate(['file' => ['required', 'file', 'max:5120']]);

        try {
            $count = $this->excel->import($request->user(), $request->file('file'));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Berkas Excel tidak dapat dibaca. Pastikan formatnya .xlsx yang valid.');
        }

        return back()->with('success', "{$count} artikel berhasil diimpor.");
    }

    public function aiGenerate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:5'],
            'topic' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $items = $this->ai->generatePosts((int) $data['count'], $data['topic'] ?? null);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('ai_results', array_map(fn ($item) => $item->toArray(), $items));
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['required', Rule::enum(PostCategory::class)],
            'items.*.excerpt' => ['nullable', 'string', 'max:500'],
            'items.*.content' => ['required', 'string'],
        ]);

        $count = $this->posts->createMany($request->user(), $data['items']);

        return redirect()->route('posts.index')->with('success', "{$count} artikel AI disimpan sebagai draf.");
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(PostCategory::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }
}