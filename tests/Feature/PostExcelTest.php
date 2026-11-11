<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostExcelTest extends TestCase
{
    use RefreshDatabase;

    private function xlsx(array $rows): UploadedFile
    {
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'xl');
        (new Xlsx($sheet))->save($path);

        return new UploadedFile($path, 'posts.xlsx', null, null, true);
    }

    public function test_export_downloads_xlsx_with_own_posts(): void
    {
        $user = User::factory()->create(['username' => 'budi']);
        Post::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/dashboard/posts/export');

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('artikel-budi-', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
        ob_start();
        $response->sendContent();
        $this->assertStringStartsWith('PK', ob_get_clean());
    }

    public function test_import_creates_posts_and_skips_invalid_rows(): void
    {
        $user = User::factory()->create();
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'],
            ['Artikel Satu', 'Teknologi', 'Ringkas', 'Isi satu', 'Published'],
            ['Artikel Dua', 'kategori-aneh', '', 'Isi dua', 'Draft'],
            ['', 'Umum', '', 'Tanpa judul', ''],
            ['Tanpa isi', 'Umum', '', '', ''],
        ]);

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('success', '2 artikel berhasil diimpor.');

        $this->assertDatabaseHas('posts', ['title' => 'Artikel Satu', 'category' => 'Teknologi', 'is_published' => true, 'user_id' => $user->id]);
        $this->assertDatabaseHas('posts', ['title' => 'Artikel Dua', 'category' => 'Umum', 'is_published' => false]);
    }

    public function test_import_rejects_unreadable_file_and_missing_file(): void
    {
        $user = User::factory()->create();
        $garbage = UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan excel');

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $garbage])->assertSessionHas('error');
        $this->actingAs($user)->post('/dashboard/posts/import', [])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('posts', 0);
    }
}
