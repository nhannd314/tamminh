@props([
    'product',
])

{{-- @var \App\Models\Product $product --}}
@php
    $cover = $product->coverUrl();
@endphp

<article {{ $attributes->class('group flex min-w-0 flex-col overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-soft-md') }}>
    <a href="{{ route('products.show', $product) }}" class="relative block" aria-label="{{ $product->name }}">
        <x-products.cover :product="$product" :cover="$cover" />
        @if ($product->badge)
            <span class="pointer-events-none absolute top-3 left-3 rounded-full bg-navy-500 px-2.5 py-1 text-sm font-semibold text-cream-50">{{ $product->badge }}</span>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach ($product->elements as $element)
                @unless ($loop->first)
                    <span class="text-ink-200">·</span>
                @endunless
                <span class="inline-flex items-center gap-1 text-sm text-ink-400">
                    <span class="size-2 rounded-full" style="background: {{ $element->color() }}"></span>{{ $element->getLabel() }}
                </span>
            @endforeach
        </div>
        <h3 class="font-serif text-lg leading-snug font-semibold text-ink-600">
            <a href="{{ route('products.show', $product) }}" class="hover:text-copper-700">{{ $product->name }}</a>
        </h3>
        <p class="flex-1 text-base leading-relaxed text-ink-400">{{ $product->summary }}</p>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
            <span class="text-lg font-semibold text-copper-600">{{ number_format($product->price, 0, ',', '.') }}đ</span>
            <form method="POST" action="{{ route('cart.store') }}" data-add-to-cart>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->getKey() }}">
                <input type="hidden" name="quantity" value="1">
                <x-ui.button type="submit" size="sm" icon="plus" :icon-size="15" class="min-h-10" :disabled="$product->stock < 1">Thêm</x-ui.button>
            </form>
        </div>
    </div>
</article>
