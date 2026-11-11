<?php

namespace Tests\Unit;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_values_options_and_labels(): void
    {
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
        $this->assertCount(6, PostCategory::options());
        $this->assertSame('Teknologi & Koding', PostCategory::Teknologi->label());
        $this->assertSame('Pendidikan & Tutorial', PostCategory::Pendidikan->label());
        $this->assertSame('Gaya Hidup & Produktivitas', PostCategory::GayaHidup->label());
        $this->assertSame('Bisnis & Karir', PostCategory::Bisnis->label());
        $this->assertSame('Opini & Pemikiran', PostCategory::Opini->label());
        $this->assertSame('Umum', PostCategory::Umum->label());
    }

    public function test_from_label_is_tolerant(): void
    {
        $this->assertSame(PostCategory::GayaHidup, PostCategory::fromLabel(' gaya hidup '));
        $this->assertSame(PostCategory::Bisnis, PostCategory::fromLabel('Bisnis & Karir'));
        $this->assertSame(PostCategory::Umum, PostCategory::fromLabel('tidak dikenal'));
        $this->assertSame(PostCategory::Umum, PostCategory::fromLabel(null));
    }
}
