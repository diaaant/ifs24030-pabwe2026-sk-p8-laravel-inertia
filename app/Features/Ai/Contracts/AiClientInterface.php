<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;
use RuntimeException;

interface AiClientInterface
{
    /**
     * Hasilkan draf artikel.
     *
     * @return list<GeneratedPostItem>
     *
     * @throws RuntimeException bila layanan AI gagal / belum dikonfigurasi
     */
    public function generatePosts(int $count, ?string $topic = null): array;
}
