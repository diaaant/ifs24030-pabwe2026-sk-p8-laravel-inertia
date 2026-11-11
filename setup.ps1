# Windows PowerShell: terapkan overlay ke proyek Laravel yang SUDAH dibuat dengan `laravel new`.
# Jalankan dari DALAM folder proyek Laravel (yang berisi artisan), mis.:
#   cd "D:\SARJANA SEMESTER 5\P8\ifs24030-pabwe2026-sk-p8-laravel-inertia"
#   powershell -ExecutionPolicy Bypass -File "<folder-zip>\setup.ps1"
$ErrorActionPreference = 'Stop'
$src = $PSScriptRoot

if (-not (Test-Path artisan)) { throw "Jalankan dari folder proyek Laravel (artisan tidak ditemukan)." }

robocopy $src $PWD /E /XF setup.sh setup.ps1 README.md composer.json composer.lock package-lock.json .env /XD node_modules vendor .git | Out-Null

# PENTING: '^' dihilangkan Windows -> pakai 2.*, dan --no-scripts agar tidak dibatalkan Composer
composer require "inertiajs/inertia-laravel:2.*" phpoffice/phpspreadsheet --no-scripts
composer dump-autoload
php artisan package:discover --ansi

npm install
npm run build

if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate

# php.ini Herd Lite tidak punya pdo_pgsql -> pakai SQLite untuk lokal
if (-not ((php -m) -match 'pdo_pgsql')) {
  (Get-Content .env) | ForEach-Object {
    if ($_ -match '^DB_CONNECTION=') { 'DB_CONNECTION=sqlite' }
    elseif ($_ -match '^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=') { '# ' + $_ }
    else { $_ }
  } | Set-Content .env
  New-Item database\database.sqlite -ItemType File -Force | Out-Null
}

if (-not (Select-String -Path config\app.php -Pattern force_https -Quiet)) {
  (Get-Content config\app.php) | ForEach-Object {
    $_
    if ($_ -match "'url' => env\('APP_URL'") { "    'force_https' => env('APP_FORCE_HTTPS', false)," }
  } | Set-Content config\app.php
}

php artisan optimize:clear
php artisan migrate:fresh --seed
if (-not (Test-Path public\storage)) { php artisan storage:link }
php artisan test
Write-Host "Selesai. Jalankan: php artisan serve"
