<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Meta Description untuk SEO -->
    <meta name="description" content="BlogVerse Pro, tempat membaca dan menerbitkan artikel berkualitas. Baca bebas tanpa akun atau mulai terbitkan artikel Anda sendiri.">
    
    <title inertia>{{ config('app.name', 'BlogVerse Pro') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    @inertia
</body>
</html>