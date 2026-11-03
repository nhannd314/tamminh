{{-- Nút giỏ hàng trên header, kèm số sản phẩm đang có trong giỏ (lưu ở session). --}}
@php
    $count = app(\App\Support\Cart::class)->count();
@endphp

<x-ui.button :href="route('cart.show')" variant="soft" size="icon" class="relative ml-auto sm:ml-0" :aria-label="'Giỏ hàng'.($count ? ' ('.$count.' sản phẩm)' : '')" title="Giỏ hàng" data-cart-button>
    <x-ui.icon name="cart" :size="20" />
    <span data-cart-count @class(['absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-copper-400 px-1 text-xs font-bold text-white', 'hidden' => $count < 1])>{{ $count > 99 ? '99+' : $count }}</span>
</x-ui.button>
