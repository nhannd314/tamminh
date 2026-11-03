@php
    $images = $product->imageUrls();
    $options = $product->priceOptions();
    $hasSizes = filled($product->sizes);
    $first = $options[0];
    $styles = $product->styles ?? [];
    $usage = $product->usage ?? [];
    $specs = $product->specs ?? [];
    $reviews = $product->reviews ?? [];
    $meaning = $product->meaning ? preg_split('/\R+/', trim($product->meaning)) : [];
    $inStock = $product->stock > 0;
    $money = fn (int $amount): string => number_format($amount, 0, ',', '.').'đ';
    $tabs = array_filter([
        'meaning' => $meaning ? 'Ý nghĩa' : null,
        'usage' => $usage ? 'Cách đặt & đeo' : null,
        'specs' => $specs ? 'Thông số' : null,
    ]);
    $optionClasses = 'cursor-pointer rounded-md border border-cream-300 bg-white px-4 py-2.5 text-base font-medium text-ink-500 transition-colors hover:border-copper-300 aria-pressed:border-copper-500 aria-pressed:bg-copper-50 aria-pressed:text-copper-700';
    $description = $product->description ?: $product->summary;
@endphp

<x-layouts.app :title="$product->name" :description="$description">
    @push('head')
        <link rel="canonical" href="{{ route('products.show', $product) }}">
        <meta property="og:type" content="product">
        <meta property="og:title" content="{{ $product->name }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ route('products.show', $product) }}">
        @if ($images)
            <meta property="og:image" content="{{ $images[0] }}">
        @endif
    @endpush

    <div class="tm-container pt-6 pb-28 lg:pb-16" data-product data-stock="{{ $product->stock }}">
        <form id="product-form" method="POST" action="{{ route('cart.store') }}" data-product-form data-add-to-cart>
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->getKey() }}">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="size" value="{{ $first['name'] }}">
            <input type="hidden" name="style" value="{{ $styles[0] ?? '' }}">
        </form>

        <x-ui.breadcrumb class="mb-6" :items="[
            'Trang chủ' => route('home'),
            'Vật phẩm phong thủy' => route('products.index'),
            $product->category->name => route('products.index', ['nhom' => $product->category->slug]),
            $product->name => null,
        ]" />

        <div class="grid items-start gap-8 md:grid-cols-2 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] lg:gap-12">
            <div class="flex flex-col gap-3 md:sticky md:top-24">
                <div class="relative overflow-hidden rounded-2xl border border-cream-300 bg-white">
                    @forelse ($images as $image)
                        <img src="{{ $image }}" alt="{{ $product->name }}" data-gallery-image @class(['aspect-square w-full object-cover', 'hidden' => ! $loop->first]) />
                    @empty
                        <x-products.cover :product="$product" icon-class="text-[10rem]" />
                    @endforelse
                    @if ($product->badge)
                        <span class="pointer-events-none absolute top-4 left-4 rounded-full bg-navy-500 px-3 py-1.5 text-sm font-semibold text-cream-50">{{ $product->badge }}</span>
                    @endif
                </div>

                @if (count($images) > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($images as $image)
                            <button type="button" data-gallery-thumb="{{ $loop->index }}" aria-label="Ảnh {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" class="block cursor-pointer overflow-hidden rounded-lg border border-cream-300 aria-[current=true]:outline-2 aria-[current=true]:outline-offset-2 aria-[current=true]:outline-copper-500">
                                <img src="{{ $image }}" alt="" loading="lazy" class="aspect-square w-full object-cover" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex min-w-0 flex-col">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @foreach ($product->elements as $element)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-cream-300 bg-white px-3 py-1 font-medium whitespace-nowrap text-ink-500">
                            <span class="size-2.5 rounded-full" style="background: {{ $element->color() }}"></span>Hợp mệnh {{ $element->getLabel() }}
                        </span>
                    @endforeach
                    @if ($product->sku)
                        <span class="text-ink-300">Mã: {{ $product->sku }}</span>
                    @endif
                </div>

                <h1 class="mt-4 font-serif text-3xl leading-tight font-semibold text-ink-600 sm:text-4xl">{{ $product->name }}</h1>

                @if ($product->rating || $product->sold_count)
                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-base">
                        @if ($product->rating)
                            <x-ui.stars :rating="(int) round((float) $product->rating)" class="!text-copper-400" />
                            <span class="font-semibold text-ink-600">{{ $product->rating }}</span>
                            @if ($reviews)
                                <a href="#danh-gia" class="text-ink-400 underline-offset-2 hover:text-copper-600 hover:underline">{{ number_format($product->reviews_count, 0, ',', '.') }} đánh giá</a>
                            @else
                                <span class="text-ink-400">{{ number_format($product->reviews_count, 0, ',', '.') }} đánh giá</span>
                            @endif
                            <span class="text-ink-200">·</span>
                        @endif
                        <span class="text-ink-400">Đã bán {{ number_format($product->sold_count, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="mt-5 flex items-baseline gap-3">
                    <span data-product-price class="font-serif text-3xl font-semibold text-copper-600">{{ $money($first['price']) }}</span>
                    <span data-product-compare @class(['text-lg text-ink-300 line-through', 'hidden' => ! $first['compare_price']])>{{ $first['compare_price'] ? $money($first['compare_price']) : '' }}</span>
                </div>
                <div data-product-subtotal class="mt-1 hidden text-base text-ink-400">Tạm tính: <span class="font-semibold text-ink-600"></span></div>

                @if ($product->description)
                    <p class="mt-4 text-base leading-relaxed text-ink-500 text-pretty">{{ $product->description }}</p>
                @endif

                <div class="mt-6 flex flex-col gap-5 border-t border-cream-300 pt-6">
                    @if ($hasSizes)
                        <div>
                            <div class="mb-2.5 text-base font-semibold text-ink-600">Kích thước</div>
                            <div class="flex flex-wrap gap-2" data-option-group="size">
                                @foreach ($options as $option)
                                    <button type="button" data-option data-name="{{ $option['name'] }}" data-price="{{ $option['price'] }}" data-compare="{{ $option['compare_price'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="{{ $optionClasses }}">{{ $option['name'] }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($styles)
                        <div>
                            <div class="mb-2.5 text-base font-semibold text-ink-600">Kiểu dáng</div>
                            <div class="flex flex-wrap gap-2" data-option-group="style">
                                @foreach ($styles as $style)
                                    <button type="button" data-option data-name="{{ $style }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="{{ $optionClasses }}">{{ $style }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($product->allows_engraving)
                        <div>
                            <label for="engraving" class="mb-2.5 block text-base font-semibold text-ink-600">Khắc tên (miễn phí)</label>
                            <x-ui.input id="engraving" name="engraving" form="product-form" maxlength="12" placeholder="Tối đa 12 ký tự" class="max-w-[320px] !text-base" />
                        </div>
                    @endif

                    @if ($inStock)
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex h-12 items-center rounded-md border border-cream-300 bg-white">
                                <button type="button" data-qty-minus aria-label="Giảm" class="flex h-full w-12 cursor-pointer items-center justify-center text-xl text-ink-400 hover:text-copper-600">−</button>
                                <span data-qty-value class="w-10 text-center text-base font-semibold text-ink-600">1</span>
                                <button type="button" data-qty-plus aria-label="Tăng" class="flex h-full w-12 cursor-pointer items-center justify-center text-ink-400 hover:text-copper-600"><x-ui.icon name="plus" :size="18" /></button>
                            </div>
                            <span class="text-sm text-ink-300">Còn {{ $product->stock }} sản phẩm</span>
                        </div>

                        <div class="hidden gap-3 sm:grid sm:grid-cols-2">
                            <x-ui.button type="submit" form="product-form" variant="outline" icon="plus" :icon-size="18" class="h-12 !text-base">Thêm vào giỏ</x-ui.button>
                            @if ($zaloUrl)
                                <x-ui.button :href="$zaloUrl" target="_blank" rel="noopener" class="h-12 !text-base">Mua ngay</x-ui.button>
                            @endif
                        </div>
                    @else
                        <p class="rounded-md bg-cream-200 px-4 py-3 text-base font-medium text-ink-500">Vật phẩm tạm hết hàng. Nhắn Zalo để được báo khi có hàng trở lại.</p>
                    @endif

                    @if ($zaloUrl)
                        <a href="{{ $zaloUrl }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl border border-cream-300 bg-cream-50 p-4 transition-colors hover:border-copper-300">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-[#0068FF] text-white"><x-ui.brand-icon network="zalo" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-base font-semibold text-ink-600">Chưa chắc hợp mệnh?</span>
                                <span class="block text-sm text-ink-400">Nhắn Zalo để Thầy tư vấn theo năm sinh – miễn phí</span>
                            </span>
                            <x-ui.icon name="chevron-down" :size="18" class="-rotate-90 text-ink-300" />
                        </a>
                    @endif
                </div>

                <ul class="mt-6 grid gap-3 border-t border-cream-300 pt-6 text-base text-ink-500 sm:grid-cols-2">
                    @foreach ([['badge', 'Khai quang trước khi giao'], ['shield', 'Cam kết chính hãng, có kiểm định'], ['send', 'Giao toàn quốc 2–4 ngày'], ['check', 'Đổi trả trong 7 ngày']] as [$icon, $label])
                        <li class="flex items-center gap-2.5"><x-ui.icon :name="$icon" :size="20" class="text-copper-600" />{{ $label }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if ($tabs)
            <section class="mt-12 rounded-2xl border border-cream-300 bg-white shadow-soft-sm" data-tabs>
                <div role="tablist" class="flex gap-6 overflow-x-auto border-b border-cream-300 px-6 sm:px-8" style="scrollbar-width: none">
                    @foreach ($tabs as $key => $label)
                        <button type="button" role="tab" data-tab="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="-mb-px shrink-0 cursor-pointer border-b-2 border-transparent py-4 text-base font-semibold whitespace-nowrap text-ink-400 hover:text-ink-600 aria-selected:border-copper-400 aria-selected:text-ink-600">{{ $label }}</button>
                    @endforeach
                </div>

                <div class="p-6 text-base leading-relaxed text-ink-500 sm:p-8">
                    @if ($meaning)
                        <div data-tab-panel="meaning" class="flex max-w-[760px] flex-col gap-4" @unless (array_key_first($tabs) === 'meaning') hidden @endunless>
                            @foreach ($meaning as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if ($usage)
                        <div data-tab-panel="usage" class="max-w-[760px]" @unless (array_key_first($tabs) === 'usage') hidden @endunless>
                            <ul class="flex flex-col gap-3">
                                @foreach ($usage as $item)
                                    <li class="flex gap-3"><x-ui.icon name="check" :size="18" class="mt-0.5 text-copper-600" /><span>{{ $item }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($specs)
                        <div data-tab-panel="specs" @unless (array_key_first($tabs) === 'specs') hidden @endunless>
                            <dl class="grid max-w-[560px] grid-cols-[auto_1fr] gap-x-8 gap-y-3">
                                @foreach ($specs as $spec)
                                    <dt class="text-ink-300">{{ $spec['label'] }}</dt>
                                    <dd class="font-medium text-ink-600">{{ $spec['value'] }}</dd>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($reviews)
            <section id="danh-gia" class="mt-6 scroll-mt-24 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm sm:p-8">
                <div class="flex flex-wrap items-end justify-between gap-2">
                    <h2 class="font-serif text-2xl font-semibold text-ink-600">Đánh giá từ khách hàng</h2>
                    @if ($product->rating)
                        <span class="text-sm text-ink-300">{{ $product->rating }}/5 · {{ number_format($product->reviews_count, 0, ',', '.') }} đánh giá</span>
                    @endif
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($reviews as $review)
                        <figure class="flex flex-col gap-3 rounded-xl border border-cream-300 bg-cream-50 p-6">
                            <x-ui.stars :rating="(int) ($review['rating'] ?? 5)" class="!text-copper-400" />
                            <blockquote class="flex-1 text-base leading-relaxed text-ink-500">{{ $review['quote'] }}</blockquote>
                            <figcaption class="text-sm">
                                <span class="font-semibold text-ink-600">{{ $review['name'] }}</span>
                                @if (filled($review['note'] ?? null))
                                    <span class="text-ink-300"> · {{ $review['note'] }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-12">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <h2 class="font-serif text-2xl font-semibold text-ink-600 sm:text-3xl">Vật phẩm liên quan</h2>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-copper-600 hover:text-copper-700">Xem tất cả →</a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <a href="{{ route('products.show', $item) }}" class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-soft-md">
                            <x-products.cover :product="$item" :cover="$item->coverUrl()" icon-class="text-6xl" />
                            <div class="flex flex-1 flex-col gap-1 p-4">
                                <span class="text-sm text-ink-400">{{ $item->elementLabels() }}</span>
                                <h3 class="font-serif text-lg leading-snug font-semibold text-ink-600 group-hover:text-copper-700">{{ $item->name }}</h3>
                                <span class="mt-auto pt-1 text-lg font-semibold text-copper-600">{{ $money($item->price) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if ($inStock)
        <div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 gap-3 border-t border-cream-300 bg-white/95 px-4 py-3 backdrop-blur sm:hidden">
            <x-ui.button type="submit" form="product-form" variant="outline" class="h-12 !text-base">Thêm vào giỏ</x-ui.button>
            @if ($zaloUrl)
                <x-ui.button :href="$zaloUrl" target="_blank" rel="noopener" class="h-12 !text-base">Mua ngay</x-ui.button>
            @endif
        </div>
    @endif
</x-layouts.app>
