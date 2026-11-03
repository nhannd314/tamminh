@php
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $orderText = $lines->isEmpty() ? '' : "Đơn hàng Tâm Minh\n"
        .$lines->map(fn ($line, int $index): string => ($index + 1).'. '.$line->product->name
            .($line->optionsLabel() ? ' ('.$line->optionsLabel().')' : '')
            .' × '.$line->quantity.' = '.$money($line->subtotal()))->implode("\n")
        ."\nTổng cộng: ".$money($total);
@endphp

<x-layouts.app title="Giỏ hàng" description="Giỏ hàng vật phẩm phong thủy Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush

    <div class="tm-container pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Giỏ hàng' => null]" />

        <h1 class="font-serif text-3xl leading-tight font-semibold text-ink-600 sm:text-4xl">Giỏ hàng</h1>

        @if ($lines->isEmpty())
            <section class="mt-6 flex flex-col items-center gap-4 rounded-2xl border border-cream-300 bg-white px-6 py-16 text-center shadow-soft-sm">
                <x-ui.icon-ring icon="cart" size="lg" />
                <h2 class="font-serif text-2xl font-semibold text-ink-600">Giỏ hàng đang trống</h2>
                <p class="max-w-[420px] text-base leading-relaxed text-ink-400">Chọn vật phẩm hợp bản mệnh của bạn, các Thầy sẽ khai quang trước khi giao.</p>
                <x-ui.button :href="route('products.index')" trailing-icon="arrow">Xem vật phẩm phong thủy</x-ui.button>
            </section>
        @else
            <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-8">
                <section class="rounded-2xl border border-cream-300 bg-white shadow-soft-sm" aria-label="Sản phẩm trong giỏ">
                    <ul class="divide-y divide-cream-300">
                        @foreach ($lines as $line)
                            @php($product = $line->product)
                            <li class="flex gap-4 p-4 sm:gap-5 sm:p-6">
                                <a href="{{ route('products.show', $product) }}" class="block size-24 shrink-0 overflow-hidden rounded-lg border border-cream-300 sm:size-28" aria-label="{{ $product->name }}">
                                    <x-products.cover :product="$product" :cover="$product->coverUrl()" icon-class="text-5xl" />
                                </a>

                                <div class="flex min-w-0 flex-1 flex-col gap-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h2 class="font-serif text-lg leading-snug font-semibold text-ink-600 sm:text-xl">
                                                <a href="{{ route('products.show', $product) }}" class="hover:text-copper-700">{{ $product->name }}</a>
                                            </h2>
                                            @if ($line->optionsLabel())
                                                <p class="mt-0.5 text-sm text-ink-400">{{ $line->optionsLabel() }}</p>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('cart.destroy', $line->key) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Xoá {{ $product->name }} khỏi giỏ" title="Xoá" class="flex size-9 cursor-pointer items-center justify-center rounded-md text-ink-300 transition-colors hover:bg-cream-100 hover:text-copper-700">
                                                <x-ui.icon name="trash" :size="18" />
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mt-auto flex flex-wrap items-end justify-between gap-3">
                                        <form method="POST" action="{{ route('cart.update', $line->key) }}" class="flex h-11 items-center rounded-md border border-cream-300 bg-white">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" name="quantity" value="{{ $line->quantity - 1 }}" @disabled($line->quantity <= 1) aria-label="Giảm số lượng" class="flex h-full w-11 cursor-pointer items-center justify-center text-xl text-ink-400 hover:text-copper-600 disabled:cursor-not-allowed disabled:opacity-40">−</button>
                                            <span class="w-10 text-center text-base font-semibold text-ink-600" aria-label="Số lượng">{{ $line->quantity }}</span>
                                            <button type="submit" name="quantity" value="{{ $line->quantity + 1 }}" @disabled($line->quantity >= $product->stock) aria-label="Tăng số lượng" class="flex h-full w-11 cursor-pointer items-center justify-center text-ink-400 hover:text-copper-600 disabled:cursor-not-allowed disabled:opacity-40"><x-ui.icon name="plus" :size="18" /></button>
                                        </form>

                                        <div class="text-right">
                                            @if ($line->comparePrice)
                                                <div class="text-sm text-ink-300 line-through">{{ $money($line->comparePrice * $line->quantity) }}</div>
                                            @endif
                                            <div class="text-lg font-semibold text-copper-600">{{ $money($line->subtotal()) }}</div>
                                            @if ($line->quantity > 1)
                                                <div class="text-sm text-ink-300">{{ $money($line->unitPrice) }} / sản phẩm</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <aside class="flex flex-col gap-4 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm lg:sticky lg:top-24">
                    <h2 class="font-sans text-sm font-semibold tracking-[0.14em] text-ink-600 uppercase">Tóm tắt đơn hàng</h2>

                    <dl class="flex flex-col gap-3 text-base">
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Tạm tính ({{ $lines->sum('quantity') }} sản phẩm)</dt>
                            <dd class="font-medium text-ink-600">{{ $money($total) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-400">Phí giao hàng</dt>
                            <dd class="text-right text-ink-500">Miễn phí khi chuyển khoản online</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 border-t border-cream-300 pt-4">
                            <dt class="font-semibold text-ink-600">Tạm tính</dt>
                            <dd class="font-serif text-3xl font-semibold text-copper-600">{{ $money($total) }}</dd>
                        </div>
                    </dl>

                    <x-ui.button :href="route('checkout.show')" size="lg" block trailing-icon="arrow" class="uppercase tracking-wide">Thanh toán</x-ui.button>

                    @if ($zaloUrl)
                        <div class="flex items-center gap-3 text-xs text-ink-300 uppercase"><span class="h-px flex-1 bg-cream-300"></span>hoặc<span class="h-px flex-1 bg-cream-300"></span></div>
                        <a href="{{ $zaloUrl }}" target="_blank" rel="noopener" class="tm-btn tm-btn-md w-full bg-[#0068FF] !text-base !text-white shadow-soft-sm hover:bg-[#0054D1]">
                            <x-ui.brand-icon network="zalo" />Đặt hàng qua Zalo
                        </a>
                        <x-ui.button variant="outline" icon="send" :icon-size="16" class="w-full !text-base" data-copy="{{ $orderText }}" data-copy-message="Đã chép nội dung đơn hàng - dán vào Zalo để chốt đơn.">Sao chép đơn hàng</x-ui.button>
                        <p class="text-sm leading-relaxed text-ink-400">Muốn chốt đơn qua Zalo: sao chép đơn hàng rồi dán vào khung chat để Thầy xác nhận.</p>
                    @endif

                    <ul class="grid gap-2.5 border-t border-cream-300 pt-4 text-sm text-ink-500">
                        @foreach ([['badge', 'Khai quang trước khi giao'], ['send', 'Giao toàn quốc 2–4 ngày'], ['check', 'Đổi trả trong 7 ngày']] as [$icon, $label])
                            <li class="flex items-center gap-2.5"><x-ui.icon :name="$icon" :size="18" class="text-copper-600" />{{ $label }}</li>
                        @endforeach
                    </ul>

                    <a href="{{ route('products.index') }}" class="text-center text-sm font-semibold text-copper-600 hover:text-copper-700">← Tiếp tục mua sắm</a>
                </aside>
            </div>
        @endif
    </div>
</x-layouts.app>
