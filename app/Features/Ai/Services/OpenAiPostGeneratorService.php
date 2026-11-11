<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generatePosts(int $count, ?string $topic = null): array
    {
        $key = config('openai.api_key');
        if (! $key) {
            throw new RuntimeException('OPENAI_API_KEY belum diatur pada file .env.');
        }

        try {
            $response = Http::withToken($key)
                ->timeout((int) config('openai.timeout', 60))
                ->post(rtrim(config('openai.base_url'), '/').'/chat/completions', [
                    'model' => config('openai.model'),
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.8,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->userPrompt($count, $topic)],
                    ],
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Tidak dapat menghubungi layanan OpenAI.');
        }

        if ($response->failed()) {
            throw new RuntimeException('OpenAI menolak permintaan (HTTP '.$response->status().').');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $posts = is_array($decoded) ? ($decoded['posts'] ?? null) : null;

        if (! is_array($posts) || $posts === []) {
            throw new RuntimeException('Respons OpenAI tidak berisi artikel yang valid.');
        }

        return array_values(array_map(
            fn ($item) => GeneratedPostItem::fromArray((array) $item),
            array_slice($posts, 0, $count)
        ));
    }

    private function systemPrompt(): string
    {
        $categories = implode(', ', PostCategory::values());

        return 'Kamu adalah penulis blog berbahasa Indonesia. Balas HANYA dengan JSON: '
            .'{"posts":[{"title":string,"category":string,"excerpt":string,"content":string}]}. '
            ."category harus salah satu dari: {$categories}. "
            .'excerpt berisi 1-2 kalimat pemikat. content berformat Markdown (heading, paragraf, daftar) minimal 3 paragraf.';
    }

    private function userPrompt(int $count, ?string $topic): string
    {
        $subject = $topic ? "bertema \"{$topic}\"" : 'dengan topik beragam';

        return "Buat {$count} draf artikel blog {$subject}.";
    }
}
