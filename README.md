# BlogVerse Pro — Studi Kasus P8 (Laravel 13 + Inertia.js + React)

Platform publikasi artikel 3 tingkat (Publik, Penulis, Admin) dengan arsitektur **Feature-First / Clean Architecture**,
PostgreSQL, PhpSpreadsheet (ekspor/impor Excel), TOAST UI Editor/Viewer, dan integrasi OpenAI.

> Folder ini adalah **overlay**: berisi semua file yang dibuat/diubah di atas proyek hasil `laravel new`
> (sama seperti alur modul: "tiap tahap menyebut file yang perlu dibuat/diubah").
> Ganti nama folder/zip `username-…` dengan username Anda, mis. `abdullah_ubaid-pabwe2026-sk-p8-laravel-inertia`.

## 1. Pasang (otomatis)

```bash
unzip username-pabwe2026-sk-p8-laravel-inertia.zip
cd username-pabwe2026-sk-p8-laravel-inertia
bash setup.sh
```

`setup.sh` menjalankan `laravel new … --database=pgsql --phpunit --no-node --no-boost --no-interaction`, menyalin overlay,
`composer require inertiajs/inertia-laravel:^2.0 phpoffice/phpspreadsheet`, `npm install && npm run build`,
menambah `force_https` di `config/app.php`, membuat database `pabwe_p8_blog`, migrasi + seed, dan `storage:link`.

## 2. Pasang (manual, Windows)

```bash
laravel new USERNAME-pabwe2026-sk-p8-laravel-inertia --database=pgsql --phpunit --no-node --no-boost --no-interaction
```
1. Salin seluruh isi folder ini ke proyek tersebut (timpa file yang sama).
2. `composer require inertiajs/inertia-laravel:^2.0 phpoffice/phpspreadsheet`
3. `npm install` lalu `npm run build`
4. Di `config/app.php` tambahkan: `'force_https' => env('APP_FORCE_HTTPS', false),`
5. `copy .env.example .env`, `php artisan key:generate`, isi `OPENAI_API_KEY` bila ingin memakai AI.
6. `psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"`
7. `php artisan migrate:fresh --seed` · `php artisan storage:link` · `php artisan serve`

Akun seeder: **admin / admin** (Admin) · **member / member** (Penulis).

## 3. Peta file terhadap modul

| Bagian modul | File |
|---|---|
| 3.1 .env / database | `.env.example` |
| 3.2 Tiga tingkat akses | `routes/web.php`, `app/Http/Middleware/EnsureRole.php`, `EnsurePostOwnership.php` |
| 3.3 Model & skema `posts` | `database/migrations/2026_11_09_000003_create_posts_table.php`, `app/Features/Post/Models/Post.php`, `Enums/PostCategory.php`, factory & seeder |
| 3.4 Struktur Feature-First | `app/Features/{Admin,Ai,Auth,Post,Profile}/…`, `resources/js/{Layouts,Pages}/…` (persis seperti modul) |
| 3.5 Daftar rute | `routes/web.php` (+ `PUT /profile` dan `DELETE /admin/users/{user}` untuk "kelola pengguna") |
| 3.6 UI/UX | `resources/js/Layouts/AppLayout.jsx`, `Pages/*`, `Components/*` |
| 3.7 Kriteria mutu | ID login `login-username/password/submit`; `.gitignore` `*`+`!.gitignore` di setiap `storage/**`; `phpunit.xml`; `tests/` |

Catatan desain:
- `PostRepositoryInterface` ↔ `PostRepository` dan `AiClientInterface` ↔ `OpenAiPostGeneratorService` di-bind di `AppServiceProvider`.
- Pencarian memakai `LOWER(col) LIKE ?` agar portabel (PostgreSQL & SQLite untuk tes).
- Ekspor Excel menulis semua sel sebagai string (mencegah formula injection). Impor membaca kolom: Judul, Kategori, Ringkasan, Konten, Status.
- Hasil AI dikirim via flash session lalu dipilih pengguna dan disimpan sebagai **draf** (`ai-save`).
- Jumlah artikel AI dibatasi 1–5 (input `type="number"`, divalidasi juga di server).

## 4. Pengujian

```bash
php artisan test --coverage --min=100     # atau jalankan test.bat
composer audit
```
`phpunit.xml` memakai SQLite in-memory agar cepat; ubah `DB_CONNECTION`/`DB_DATABASE` bila ingin menguji ke PostgreSQL.
