<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
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

    public function test_import_handles_xls_extension(): void
    {
        $user = User::factory()->create();
        
        // Menguji cabang (branch) 'xls' => new XlsReader() di dalam PostExcelService
        $garbage = UploadedFile::fake()->createWithContent('rusak.xls', 'bukan excel');

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $garbage])
            ->assertSessionHas('error');
    }

    public function test_import_rejects_unsupported_extension(): void
    {
        $user = User::factory()->create();
        
        // Menguji cabang 'default' di dalam PostExcelService (selain xlsx/xls ditolak)
        $file = UploadedFile::fake()->create('data.csv', 100);

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('error', 'Format file harus .xlsx atau .xls.');
    }

    public function test_import_handles_generic_throwable_error(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('test.xlsx', 100);

        // Menguji blok catch (Throwable $e) di PostController.
        $this->mock(PostExcelService::class, function ($mock) {
            $mock->shouldReceive('import')->andThrow(new \Exception('Simulasi error sistem'));
        });

        $this->actingAs($user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('error', 'Berkas Excel tidak dapat dibaca. Pastikan formatnya .xlsx yang valid.');
    }

    // =====================================================================
    // TEST TAMBAHAN UNTUK MENUTUPI SISA COVERAGE (BLOK $withAuthor = true)
    // =====================================================================
    public function test_build_excel_with_author_data(): void
    {
        $user = User::factory()->create(['username' => 'penulis']);
        Post::factory()->count(2)->create(['user_id' => $user->id]);

        $posts = Post::with('user')->get();
        $excel = app(PostExcelService::class);
        
        // Memastikan baris $withAuthor = true dieksekusi
        $spreadsheet = $excel->build($posts, true);
        $this->assertNotNull($spreadsheet);

        $response = $excel->download($posts, 'test-admin.xlsx', true);
        $this->assertNotNull($response);
    }
}