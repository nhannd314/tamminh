{{--
    Khung trái của các trang tài khoản: tóm tắt hồ sơ, menu điều hướng và nút đăng xuất.
    Usage: <x-account.sidebar :user="$user" />
--}}
@props([
    'user',
])


<aside {{ $attributes->class('flex flex-col gap-5 rounded-2xl border border-cream-300 bg-white p-5 shadow-soft-sm sm:p-6 lg:sticky lg:top-24') }}>
    <div class="flex items-center gap-4">
        <x-ui.avatar size="xl">{{ $user->avatar_initial }}</x-ui.avatar>
        <div class="min-w-0">
            <div class="truncate font-serif text-xl font-semibold text-ink-600">{{ $user->name ?: 'Khách hàng' }}</div>
            <p class="truncate text-sm text-ink-400">{{ $user->email }}</p>
        </div>
    </div>

    <nav class="flex flex-col gap-1 border-t border-cream-300 pt-4" aria-label="Menu tài khoản">
        @foreach (\App\Support\AccountMenu::links() as ['route' => $route, 'icon' => $icon, 'label' => $label])
            <a href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif @class([
                'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors',
                'bg-copper-50 text-copper-700' => request()->routeIs($route),
                'text-ink-500 hover:bg-cream-100 hover:text-ink-600' => ! request()->routeIs($route),
            ])>
                <x-ui.icon :name="$icon" :size="18" @class(['text-copper-500' => request()->routeIs($route), 'text-ink-300' => ! request()->routeIs($route)]) />
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="border-t border-cream-300 pt-4">
        @csrf
        <x-ui.button type="submit" variant="outline" block>Đăng xuất</x-ui.button>
    </form>
</aside>
