#!/usr/bin/env bash
# Menerapkan overlay proyek ini ke proyek Laravel baru (sesuai modul P8).
# Pemakaian:  bash setup.sh   (dijalankan dari folder hasil ekstrak zip)
set -euo pipefail

NAME="${1:-$(basename "$PWD")}"
PARENT="$(dirname "$PWD")"
SRC="$PWD"

command -v laravel >/dev/null || composer global require laravel/installer

cd "$PARENT"
if [ ! -d "$NAME-laravel" ]; then
  laravel new "$NAME-laravel" --database=pgsql --phpunit --no-node --no-boost --no-interaction
fi
cd "$NAME-laravel"

# Salin overlay (timpa file bawaan skeleton)
rsync -a --exclude 'setup.sh' --exclude 'README.md' "$SRC"/ ./

composer require inertiajs/inertia-laravel:^2.0 phpoffice/phpspreadsheet
npm install
npm run build

# config/app.php -> force_https
grep -q force_https config/app.php || \
  sed -i "/'url' => env('APP_URL'/a\\    'force_https' => env('APP_FORCE_HTTPS', false)," config/app.php

cp -n .env.example .env 2>/dev/null || true
grep -q '^APP_KEY=base64' .env || php artisan key:generate

psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;" || true
php artisan migrate:fresh --seed
php artisan storage:link
echo "Selesai. Jalankan: cd $NAME-laravel && php artisan serve"
