@php
    $cover = $post->coverUrl();
@endphp

<x-layouts.app :title="$post->title" :description="$post->meta_description ?: $post->excerpt">
    @push('head')
        <link rel="canonical" href="{{ route('posts.show', $post) }}">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $post->title }}">
        <meta property="og:description" content="{{ $post->meta_description ?: $post->excerpt }}">
        <meta property="og:url" content="{{ route('posts.show', $post) }}">
        <meta property="og:image" content="{{ $cover ?? asset('images/hero-mountains.webp') }}">
        <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endpush

    <article class="pt-6 pb-14 lg:pb-20">
        <div class="tm-container">
            <div class="mx-auto max-w-[760px]">
                <x-ui.breadcrumb class="mb-8" :items="['Trang chủ' => route('home'), 'Bài viết' => route('posts.index'), $post->category->name => route('posts.category', $post->category)]" />

                <header class="flex flex-col items-start gap-5">
                    <a href="{{ route('posts.category', $post->category) }}" class="tm-eyebrow">{{ $post->category->name }}</a>
                    <h1 class="font-serif text-3xl leading-tight font-semibold tracking-[-0.01em] text-balance text-ink-600 sm:text-4xl lg:text-5xl">{{ $post->title }}</h1>
                    <p class="tm-lead max-w-[680px]">{{ $post->excerpt }}</p>
                    <div class="flex flex-wrap items-center gap-1.5 text-sm text-ink-300">
                        <span class="font-semibold text-copper-700">{{ $post->author_name }}</span>
                        <span class="text-ink-200">•</span>
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->publishedDate() }}</time>
                        <span class="text-ink-200">•</span>
                        <span>{{ $post->readingMinutes() }} phút đọc</span>
                    </div>
                </header>
            </div>

            <div @class(['relative mx-auto mt-10 flex aspect-[16/7] max-w-[1000px] items-center justify-center overflow-hidden rounded-2xl', $post->tone->coverClasses() => ! $cover])>
                @if ($cover)
                    <img src="{{ $cover }}" alt="{{ $post->title }}" class="size-full object-cover" />
                @else
                    <span class="font-serif text-[9rem] leading-none font-medium text-shadow-sm sm:text-[12rem]" aria-hidden="true">{{ $post->glyph }}</span>
                @endif
            </div>

            <div class="tm-prose mx-auto mt-10 lg:mt-12">
                {{ $post->getRichContentAttribute('content') }}
            </div>

            <aside class="mx-auto mt-12 flex max-w-[760px] flex-col items-center gap-4 rounded-2xl bg-navy-500 p-7 text-center sm:flex-row sm:justify-between sm:p-8 sm:text-left">
                <div>
                    <div class="font-serif text-2xl font-semibold text-cream-50">Xem tướng khuôn mặt của bạn</div>
                    <p class="mt-1.5 text-base text-cream-50/80">Tải ảnh chân dung để nhận bản luận giải nhân tướng riêng, phần tổng quan miễn phí.</p>
                </div>
                <x-ui.button :href="url('/#xem-tuong')" size="lg" class="shrink-0">Xem tướng ngay</x-ui.button>
            </aside>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-cream-300 bg-cream-100 py-12 lg:py-16">
            <div class="tm-container">
                <h2 class="mb-8 text-center font-serif text-2xl font-semibold text-ink-600 sm:text-3xl">Bài viết cùng chuyên mục</h2>
                <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 lg:grid-cols-3 lg:gap-[22px]">
                    @foreach ($related as $item)
                        <x-cards.post :post="$item->card()" :href="route('posts.show', $item)" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
