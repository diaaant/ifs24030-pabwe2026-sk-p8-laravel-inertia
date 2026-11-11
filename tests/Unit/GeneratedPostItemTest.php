<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_from_array_normalizes_values(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => ' Judul ', 'category' => 'opini', 'excerpt' => ' Ringkas ', 'content' => ' Isi ']);

        $this->assertSame(['title' => 'Judul', 'category' => 'Opini', 'excerpt' => 'Ringkas', 'content' => 'Isi'], $item->toArray());
    }

    public function test_from_array_uses_defaults_for_missing_keys(): void
    {
        $item = GeneratedPostItem::fromArray([]);

        $this->assertSame('Artikel Tanpa Judul', $item->title);
        $this->assertSame('Umum', $item->category);
        $this->assertNull($item->excerpt);
        $this->assertSame('', $item->content);
    }
}
