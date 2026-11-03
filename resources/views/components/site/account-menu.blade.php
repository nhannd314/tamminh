{{--
    Nút "Tài khoản" trên header mở menu thả xuống: chưa đăng nhập thì có Đăng nhập / Đăng ký tài khoản,
    đã đăng nhập thì có các trang tài khoản và Đăng xuất.
    Usage: <x-site.account-menu /> (chỉ hiện từ lg; màn nhỏ dùng menu di động)
--}}
@php
    $user = auth('customer')->user();
    $itemClasses = fn (bool $active): string => $active
        ? 'flex items-center gap-3 px-4 py-2.5 text-sm transition-colors bg-copper-50 font-semibold text-copper-700'
        : 'flex items-center gap-3 px-4 py-2.5 text-sm transition-colors text-ink-600 hover:bg-cream-100 hover:text-copper-700';
    $links = $user === null
        ? [
            ['route' => 'login', 'icon' => 'user', 'label' => 'Đăng nhập'],
            ['route' => 'register', 'icon' => 'plus', 'label' => 'Đăng ký tài khoản'],
        ]
        : \App\Support\AccountMenu::links();
@endphp

<div data-dropdown class="relative mr-0.5 hidden lg:block">
    <button type="button" data-dropdown-trigger aria-haspopup="menu" aria-expanded="false" aria-controls="account-menu"
        class="flex cursor-pointer items-center gap-1 text-sm whitespace-nowrap text-ink-500 transition-colors hover:text-copper-600 aria-expanded:text-copper-600"
        @if ($user) title="{{ $user->name ?: $user->email }}" @endif>
        Tài khoản
        <x-ui.icon name="chevron-down" :size="14" class="transition-transform duration-200 [[aria-expanded=true]>&]:rotate-180" />
    </button>

    <div id="account-menu" data-dropdown-menu role="menu" hidden
        @class([
            'absolute top-full right-0 z-50 mt-5 overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-lg',
            'w-64' => $user !== null,
            'w-52' => $user === null,
        ])>
        @if ($user)
            <div class="flex items-center gap-3 border-b border-cream-300 px-4 py-3.5">
                <x-ui.avatar size="md" data-user-avatar>{{ $user->avatar_initial }}</x-ui.avatar>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-ink-600">{{ $user->name ?: 'Khách hàng' }}</div>
                    <div class="truncate text-xs text-ink-400">{{ $user->email }}</div>
                </div>
            </div>
        @endif

        <div class="py-1.5">
            @foreach ($links as ['route' => $route, 'icon' => $icon, 'label' => $label])
                <a href="{{ route($route) }}" role="menuitem" class="{{ $itemClasses(request()->routeIs($route)) }}">
                    <x-ui.icon :name="$icon" :size="17" class="text-copper-500" />
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($user)
            <form method="POST" action="{{ route('logout') }}" class="border-t border-cream-300 py-1.5">
                @csrf
                <button type="submit" role="menuitem" class="flex w-full cursor-pointer items-center gap-3 px-4 py-2.5 text-left text-sm text-ink-600 transition-colors hover:bg-cream-100 hover:text-danger">
                    <x-ui.icon name="arrow-left" :size="17" class="text-ink-300" />
                    Đăng xuất
                </button>
            </form>
        @endif
    </div>
</div>
