<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tes tidak membutuhkan hasil build Vite.
        $this->withoutVite();
    }

    /** Gambar PNG 1x1 tanpa ekstensi GD (aman walau GD tanpa dukungan JPEG). */
    protected function fakeImage(string $name = 'cover.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent(pathinfo($name, PATHINFO_FILENAME).'.png', $png);
    }
}
