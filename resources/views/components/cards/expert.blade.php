@props([
    'expert',
])

{{-- @var \App\Models\Expert $expert --}}
@php
    $photo = $expert->photoUrl();
    $profileUrl = route('experts.show', $expert);
@endphp

<article {{ $attributes->class('flex items-stretch gap-4 rounded-[10px] border border-cream-300 bg-white p-4 sm:flex-col sm:p-5 xl:flex-row xl:gap-5 shadow-soft-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md') }}>
    <a href="{{ $profileUrl }}" class="block shrink-0 rounded-lg" tabindex="-1" aria-hidden="true">
        @if ($photo)
            <img src="{{ $photo }}" alt="{{ $expert->name }}" loading="lazy" class="size-28 shrink-0 rounded-lg bg-cream-200 object-cover object-top min-[400px]:size-36 sm:h-52 sm:w-full xl:size-40" />
        @else
            <x-ui.glyph tone="navy" size="xl">{{ $expert->glyph }}</x-ui.glyph>
        @endif
    </a>

    <div class="flex min-w-0 flex-1 flex-col">
        <h3 class="mb-1 text-lg leading-tight font-semibold text-ink-600 sm:text-xl">
            <a href="{{ $profileUrl }}" class="transition-colors hover:text-copper-600">{{ $expert->name }}</a>
        </h3>
        <div class="mt-2.5 mb-4 flex flex-col gap-1 text-sm text-ink-500">
            <span>Kinh nghiệm: <b class="font-medium">{{ $expert->years_experience }} năm</b></span>
            <span>Lượt tư vấn: <b class="font-medium">{{ number_format($expert->sessions_count, 0, ',', '.') }}</b></span>
        </div>
        <x-ui.button
            :href="$profileUrl"
            trailing-icon="arrow"
            class="mt-auto self-start py-2.5 uppercase tracking-wide"
        >Chi tiết</x-ui.button>
    </div>
</article>
