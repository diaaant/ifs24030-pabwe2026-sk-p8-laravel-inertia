<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

final readonly class GeneratedPostItem
{
    public function __construct(
        public string $title,
        public string $category,
        public ?string $excerpt,
        public string $content,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            title: mb_substr(trim((string) ($data['title'] ?? 'Artikel Tanpa Judul')), 0, 255),
            category: PostCategory::fromLabel($data['category'] ?? null)->value,
            excerpt: isset($data['excerpt']) ? mb_substr(trim((string) $data['excerpt']), 0, 500) : null,
            content: trim((string) ($data['content'] ?? '')),
        );
    }

    /** @return array{title: string, category: string, excerpt: ?string, content: string} */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
