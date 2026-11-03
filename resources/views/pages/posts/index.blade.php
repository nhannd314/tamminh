@php
    $heading = $category?->name ?? 'Kiến thức tướng số';
    $intro = $category?->description ?: 'Các Thầy của Tâm Minh chia sẻ bài luận về tướng số, tử vi và phong thủy - viết bằng ngôn ngữ gần gũi, ứng dụng được vào đời sống hiện đại.';
    $chipClasses = 'rounded-full border px-4 py-2 text-sm font-semibold transition-colors';
@endphp

<x-layouts.app :title="$category ? $category->name.' - Bài viết' : 'Bài viết & tư liệu'" :description="$intro">
    @if ($posts->currentPage() > 1)
        @push('head')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    <section class="border-b border-cream-300 bg-cream-100 pt-6 pb-10 lg:pb-14">
        <div class="tm-container">
            <x-ui.breadcrumb class="mb-8" :items="$category
                ? ['Trang chủ' => route('home'), 'Bài viết' => route('posts.index'), $category->name => null]
                : ['Trang chủ' => route('home'), 'Bài viết' => null]" />

            <x-ui.section-heading eyebrow="Bài viết & tư liệu" :title="$heading" size="md" as="h1" align="left" class="max-w-[820px]">
                {{ $intro }}
            </x-ui.section-heading>

            @if ($categories->isNotEmpty())
                <nav class="mt-8 flex flex-wrap gap-2" aria-label="Chuyên mục">
                    <a href="{{ route('posts.index') }}" @class([$chipClasses, 'border-copper-400 bg-copper-400 text-white' => ! $category, 'border-cream-300 bg-white text-ink-500 hover:border-copper-300 hover:text-copper-600' => $category])
                        @unless ($category) aria-current="page" @endunless>Tất cả</a>
                    @foreach ($categories as $item)
                        <a href="{{ route('posts.category', $item) }}" @class([$chipClasses, 'border-copper-400 bg-copper-400 text-white' => $category?->is($item), 'border-cream-300 bg-white text-ink-500 hover:border-copper-300 hover:text-copper-600' => ! $category?->is($item)])
                            @if ($category?->is($item)) aria-current="page" @endif>{{ $item->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="tm-container">
            @if ($posts->isEmpty())
                <p class="py-16 text-center text-lg text-ink-300">Chưa có bài viết nào trong chuyên mục này.</p>
            @else
                <div class="grid grid-cols-1 gap-[18px] sm:grid-cols-2 lg:grid-cols-3 lg:gap-[22px]">
                    @foreach ($posts as $post)
                        <x-cards.post :post="$post->card()" :href="route('posts.show', $post)" />
                    @endforeach
                </div>

                <x-ui.pagination :paginator="$posts" class="mt-10" />
            @endif
        </div>
    </section>
</x-layouts.app>
