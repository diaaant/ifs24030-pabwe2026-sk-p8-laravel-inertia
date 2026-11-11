<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function fakeOpenAi(array $posts): void
    {
        Http::fake(['api.openai.com/*' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['posts' => $posts])]]],
        ])]);
    }

    public function test_generate_flashes_results(): void
    {
        $this->fakeOpenAi([
            ['title' => 'Satu', 'category' => 'Bisnis', 'excerpt' => 'e', 'content' => 'c'],
            ['title' => 'Dua', 'category' => 'ngawur', 'content' => 'c2'],
            ['title' => 'Tiga', 'category' => 'Umum', 'excerpt' => 'e', 'content' => 'c3'],
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-generate', ['count' => 2, 'topic' => 'startup'])
            ->assertSessionHas('ai_results', fn ($r) => count($r) === 2 && $r[1]['category'] === 'Umum');

        Http::assertSent(fn ($req) => $req->hasHeader('Authorization') && str_contains($req['messages'][1]['content'], 'startup'));
    }

    public function test_generate_validates_count_limits(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 0])->assertSessionHasErrors('count');
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 6])->assertSessionHasErrors('count');
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', [])->assertSessionHasErrors('count');
    }

    public function test_generate_handles_missing_key_http_error_and_bad_payload(): void
    {
        $user = User::factory()->create();

        config(['openai.api_key' => null]);
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 1])->assertSessionHas('error');

        config(['openai.api_key' => 'k']);
        Http::fake(['api.openai.com/*' => Http::response([], 500)]);
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 1])->assertSessionHas('error');

        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => 'bukan json']]]])]);
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', ['count' => 1])->assertSessionHas('error');
    }

    public function test_generate_handles_connection_failure(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));

        $this->actingAs(User::factory()->create())->post('/dashboard/posts/ai-generate', ['count' => 1])
            ->assertSessionHas('error', 'Tidak dapat menghubungi layanan OpenAI.');
    }

    public function test_save_batch_creates_drafts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/dashboard/posts/ai-save', ['items' => [
            ['title' => 'AI Satu', 'category' => 'Opini', 'excerpt' => 'e', 'content' => 'c'],
            ['title' => 'AI Dua', 'category' => 'Umum', 'content' => 'c'],
        ]])->assertRedirect('/dashboard/posts');

        $this->assertDatabaseHas('posts', ['title' => 'AI Satu', 'is_published' => false, 'user_id' => $user->id]);
        $this->assertDatabaseCount('posts', 2);
    }

    public function test_save_batch_validates(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/dashboard/posts/ai-save', ['items' => [['title' => '', 'category' => 'x', 'content' => '']]])
            ->assertSessionHasErrors(['items.0.title', 'items.0.category', 'items.0.content']);
    }
}
