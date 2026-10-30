@props([
    'review',
])

{{-- @var array{rating: int, quote: string, name: string, role: string, service: string, avatar: string, tone: string} $review --}}
<figure {{ $attributes->class('relative flex flex-col gap-[18px] rounded-2xl border border-cream-300 bg-white p-7 transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md') }}>
    <x-ui.stars :rating="$review['rating']" />

    <blockquote class="flex-1 text-sm leading-relaxed text-ink-600">
        <span class="mr-0.5 align-[-10px] font-serif text-4xl leading-0 text-copper-300">“</span>{{ $review['quote'] }}
    </blockquote>

    <figcaption class="flex items-start gap-3.5 border-t border-cream-300 pt-[18px]">
        <x-ui.avatar :tone="$review['tone']">{{ $review['avatar'] }}</x-ui.avatar>
        <div class="min-w-0">
            <div class="text-sm leading-snug font-semibold text-ink-600">{{ $review['name'] }}</div>
            <div class="text-xs leading-snug text-ink-300">{{ $review['role'] }}</div>
            <div class="mt-1 text-xs leading-snug font-medium text-copper-600">✦ {{ $review['service'] }}</div>
        </div>
    </figcaption>
</figure>
