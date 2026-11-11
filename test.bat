@echo off
php artisan config:clear
php artisan test --coverage --min=100
composer audit
