@props([
    'title' => null,
    'description' => 'Tâm Minh — xem tướng số, chỉ tay và phong thủy online. Luận giải cá nhân hóa theo tinh hoa nhân tướng học phương Đông.',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="description" content="{{ $description }}">
        <meta name="theme-color" content="#FAF6EC">

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Tâm Minh') }}</title>

        <link rel="icon" href="{{ asset('images/logo-mark.svg') }}" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body>
        <x-site.header />
        <x-site.drawer />

        <main id="top" class="overflow-x-clip">
            {{ $slot }}
        </main>

        <x-site.footer />
        <x-site.toast />

        @stack('modals')
    </body>
</html>
