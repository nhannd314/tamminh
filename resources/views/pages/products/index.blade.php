@php
    $query = fn (array $overrides): array => array_filter(
        array_merge(['menh' => $element?->value, 'nhom' => $category?->slug, 'sap-xep' => $sort === 'pho-bien' ? null : $sort], $overrides),
        fn ($value): bool => $value !== null,
    );
    $tabClasses = 'shrink-0 whitespace-nowrap rounded-md px-3.5 py-2.5 text-base font-medium transition-colors';
@endphp

<x-layouts.app title="Vật phẩm phong thủy" description="Vật phẩm phong thủy chọn theo bản mệnh, được các Thầy khai quang trước khi giao.">
    @if ($products->currentPage() > 1)
        @push('head')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    <div class="tm-container pt-6 pb-16">
        <x-ui.breadcrumb class="mb-6" :items="['Trang chủ' => route('home'), 'Vật phẩm phong thủy' => null]" />

        <section class="flex flex-col gap-6 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-[640px]">
                <x-ui.eyebrow>Cửa hàng Tâm Minh</x-ui.eyebrow>
                <h1 class="mt-4 font-serif text-3xl leading-tight font-semibold text-ink-600 sm:text-4xl">Vật phẩm phong thủy theo bản mệnh</h1>
                <p class="mt-3 text-base leading-relaxed text-ink-500 text-pretty">Mỗi vật phẩm được các Thầy chọn theo ngũ hành tương sinh. Chọn mệnh của bạn để xem gợi ý phù hợp.</p>
            </div>
            <x-ui.button :href="route('home').'#xem-tuong'" variant="outline" trailing-icon="arrow" class="shrink-0 !text-base">Chưa biết mệnh? Xem tướng miễn phí</x-ui.button>
        </section>

        <div class="mt-6 flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-1 text-sm font-semibold tracking-[0.14em] text-ink-400 uppercase">Bản mệnh</span>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\FiveElement::cases() as $item)
                        @php($active = $element === $item)
                        <a href="{{ route('products.index', $query(['menh' => $active ? null : $item->value])) }}" @class([
                            'inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-base font-medium transition-colors',
                            'border-navy-500 bg-navy-500 text-cream-50' => $active,
                            'border-cream-300 bg-white text-ink-500 hover:border-copper-300' => ! $active,
                        ]) @if ($active) aria-current="true" @endif>
                            <span @class(['size-3 rounded-full', 'ring-2 ring-cream-50' => $active]) style="background: {{ $item->color() }}"></span>{{ $item->getLabel() }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-3 border-y border-cream-300 py-3 lg:flex-row lg:items-center lg:justify-between">
                <nav class="-mx-1 flex gap-1 overflow-x-auto px-1" style="scrollbar-width: none" aria-label="Nhóm vật phẩm">
                    <a href="{{ route('products.index', $query(['nhom' => null])) }}" @class([$tabClasses, 'bg-copper-50 text-copper-700' => ! $category, 'text-ink-400 hover:bg-cream-200' => $category])>Tất cả</a>
                    @foreach ($categories as $item)
                        <a href="{{ route('products.index', $query(['nhom' => $item->slug])) }}" @class([$tabClasses, 'bg-copper-50 text-copper-700' => $category?->is($item), 'text-ink-400 hover:bg-cream-200' => ! $category?->is($item)])>{{ $item->name }}</a>
                    @endforeach
                </nav>
                <form method="GET" action="{{ route('products.index') }}" class="flex shrink-0 items-center justify-between gap-3 lg:justify-end">
                    @if ($element)
                        <input type="hidden" name="menh" value="{{ $element->value }}">
                    @endif
                    @if ($category)
                        <input type="hidden" name="nhom" value="{{ $category->slug }}">
                    @endif
                    <span class="text-sm text-ink-300">{{ $products->total() }} vật phẩm</span>
                    <x-ui.select name="sap-xep" :selected="$sort" :options="['pho-bien' => 'Phổ biến', 'gia-tang' => 'Giá thấp → cao', 'gia-giam' => 'Giá cao → thấp']" :use-keys="true" aria-label="Sắp xếp" class="!w-auto !py-2.5 !text-base" onchange="this.form.submit()" />
                </form>
            </div>
        </div>

        @if ($products->isEmpty())
            <p class="py-16 text-center text-base text-ink-400">
                Chưa có vật phẩm phù hợp.
                <a href="{{ route('products.index') }}" class="font-semibold text-copper-600 hover:text-copper-700">Xóa bộ lọc</a>
            </p>
        @else
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-cards.product :product="$product" />
                @endforeach
            </div>

            <div class="mt-10"><x-ui.pagination :paginator="$products" /></div>
        @endif

        <section class="mt-12 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['badge', 'Khai quang trước khi giao', 'Vật phẩm được Thầy khai quang, kèm hướng dẫn đặt và đeo.'],
                ['shield', 'Đá tự nhiên có kiểm định', 'Đá quý, thạch anh đi kèm giấy kiểm định nguồn gốc.'],
                ['headset', 'Tư vấn chọn vật phẩm', 'Nhắn Zalo '.\App\Support\ShopContact::zalo().' để được Thầy gợi ý theo tuổi.'],
            ] as [$icon, $title, $text])
                <div class="flex gap-4 rounded-xl border border-cream-300 bg-white p-6">
                    <x-ui.icon-ring :icon="$icon" size="md" />
                    <div>
                        <h2 class="font-serif text-xl font-semibold text-ink-600">{{ $title }}</h2>
                        <p class="mt-1 text-base leading-relaxed text-ink-400">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</x-layouts.app>
