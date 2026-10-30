<section class="mt-12" data-reveal data-print-hidden>
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="font-serif text-2xl font-semibold text-ink-600 sm:text-3xl">Vật phẩm phong thủy cải vận</h2>
            <p class="mt-1.5 text-base text-ink-400">Gợi ý theo bản mệnh {{ $chart->destiny() }} của bạn</p>
        </div>
        <a href="#" class="tm-link text-sm font-semibold" data-toast="Cửa hàng vật phẩm phong thủy sắp ra mắt.">Xem tất cả →</a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @foreach ($products as $product)
            <div class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-sm">
                <div class="flex aspect-square items-center justify-center bg-cream-100">
                    <x-ui.glyph tone="ring" size="lg">{{ $product['glyph'] }}</x-ui.glyph>
                </div>
                <div class="flex flex-1 flex-col gap-1 p-4">
                    <h3 class="font-serif text-lg font-semibold text-ink-600">{{ $product['name'] }}</h3>
                    <p class="flex-1 text-sm leading-relaxed text-ink-400">{{ $product['description'] }}</p>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                        <x-analysis.price :amount="$product['price']" class="text-base font-semibold text-copper-600" />
                        <x-ui.button size="sm" data-toast="Cửa hàng vật phẩm phong thủy sắp ra mắt.">Mua ngay</x-ui.button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
