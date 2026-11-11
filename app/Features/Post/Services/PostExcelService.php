<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PostExcelService
{
    public const HEADERS = ['Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status'];

    private const TRUTHY = ['published', 'dipublikasikan', 'publik', 'ya', 'true', '1'];

    public function __construct(private readonly PostService $posts) {}

    public function build(Collection $posts, bool $withAuthor = false): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        $headers = $withAuthor ? [...self::HEADERS, 'Penulis', 'Username'] : self::HEADERS;

        foreach ($headers as $i => $header) {
            $sheet->setCellValueExplicit([$i + 1, 1], $header, DataType::TYPE_STRING);
        }

        $row = 2;
        foreach ($posts as $post) {
            $values = [
                $post->title,
                $post->category->value,
                (string) $post->excerpt,
                $post->content,
                $post->is_published ? 'Published' : 'Draft',
            ];

            if ($withAuthor) {
                $values[] = $post->user->name;
                $values[] = $post->user->username;
            }

            foreach ($values as $i => $value) {
                $sheet->setCellValueExplicit([$i + 1, $row], (string) $value, DataType::TYPE_STRING);
            }
            $row++;
        }

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach (['A' => 40, 'B' => 16, 'C' => 50, 'D' => 80, 'E' => 12, 'F' => 24, 'G' => 20] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        return $spreadsheet;
    }

    public function download(Collection $posts, string $filename, bool $withAuthor = false): StreamedResponse
    {
        $spreadsheet = $this->build($posts, $withAuthor);

        return response()->streamDownload(
            fn () => (new XlsxWriter($spreadsheet))->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Impor artikel (draf/publikasi sesuai kolom Status).
     *
     * @throws InvalidArgumentException bila berkas tidak dapat dibaca
     */
    public function import(User $user, UploadedFile $file): int
    {
        // Paksa reader sesuai ekstensi. JANGAN pakai IOFactory::load(), karena
        // untuk isi teks biasa IOFactory jatuh ke CSV reader (tanpa magic
        // number) dan "berhasil" membaca file sampah sebagai CSV kosong.
        $ext = strtolower($file->getClientOriginalExtension());

        $reader = match ($ext) {
            'xlsx' => new XlsxReader(),
            'xls' => new XlsReader(),
            default => throw new InvalidArgumentException('Format file harus .xlsx atau .xls.'),
        };

        try {
            $reader->setReadDataOnly(true);
            $rows = $reader->load($file->getRealPath())
                ->getActiveSheet()
                ->toArray(null, false, false, false);
        } catch (Throwable $e) {
            throw new InvalidArgumentException('Berkas Excel tidak dapat dibaca. Pastikan formatnya .xlsx yang valid.');
        }

        array_shift($rows); // baris header

        $count = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[3] ?? ''));

            if ($title === '' || $content === '') {
                continue;
            }

            $this->posts->create($user, [
                'title' => mb_substr($title, 0, 255),
                'category' => PostCategory::fromLabel($row[1] ?? null)->value,
                'excerpt' => trim((string) ($row[2] ?? '')) ?: null,
                'content' => $content,
                'is_published' => in_array(mb_strtolower(trim((string) ($row[4] ?? ''))), self::TRUTHY, true),
            ]);
            $count++;
        }

        return $count;
    }
}