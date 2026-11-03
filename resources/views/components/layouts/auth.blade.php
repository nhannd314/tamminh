{{--
    Bố cục trang đăng nhập / đăng ký / quên mật khẩu: panel thương hiệu màu navy bên trái (≥ lg) + thẻ form ở giữa.
    Usage:
    <x-layouts.auth title="Đăng nhập" max-width="420px">
        <x-slot:brand>…lời dẫn bên panel trái…</x-slot:brand>
        …thẻ form…
        <x-slot:footer>Chưa có tài khoản? …</x-slot:footer>
    </x-layouts.auth>
--}}
@props([
    'title',
    'maxWidth' => '420px',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="robots" content="noindex, nofollow">
        <meta name="theme-color" content="#FAF6EC">

        <title>{{ $title }} · {{ config('app.name', 'Tâm Minh') }}</title>

        <link rel="icon" href="{{ asset('images/logo-mark.svg') }}" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-cream-100 text-ink-500 antialiased">
        <div class="flex min-h-screen">
            <aside class="relative hidden w-[44%] flex-col justify-between overflow-hidden bg-navy-600 p-12 lg:flex">
                <img src="{{ asset('images/zodiac-wheel.svg') }}" alt="" class="pointer-events-none absolute -right-56 -bottom-56 w-[720px] max-w-none animate-[spin_60s_linear_infinite] opacity-[0.14]" />

                <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                    <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="size-12" />
                    <span class="leading-none">
                        <span class="block font-serif text-2xl font-semibold text-cream-50">Tâm Minh</span>
                        <span class="mt-1 block text-xs tracking-wide text-navy-200">Hiểu mình - An tâm sống</span>
                    </span>
                </a>

                <div class="relative max-w-md">
                    <span class="mb-6 block h-0.5 w-14 bg-copper-400"></span>
                    {{ $brand }}
                </div>

                <p class="relative text-xs text-navy-300">© {{ date('Y') }} {{ config('app.name', 'Tâm Minh') }}.</p>
            </aside>

            <main class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6">
                <div class="w-full animate-fade-up" style="max-width: {{ $maxWidth }}">
                    <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-3 lg:hidden">
                        <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="size-12" />
                        <span class="font-serif text-2xl font-semibold text-ink-600">Tâm Minh</span>
                    </a>

                    <div class="rounded-[10px] border border-cream-300 bg-white p-6 shadow-soft-md sm:p-9">
                        {{ $slot }}
                    </div>

                    @isset($footer)
                        <p class="mt-6 text-center text-sm text-ink-300">{{ $footer }}</p>
                    @endisset
                </div>
            </main>
        </div>

        <x-site.toast />
    </body>
</html>
