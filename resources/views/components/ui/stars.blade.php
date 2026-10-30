@props([
    'rating' => 5,
    'max' => 5,
    'size' => 16,
])

<div {{ $attributes->class('inline-flex gap-1 text-copper-500') }} role="img" aria-label="{{ $rating }}/{{ $max }} sao">
    @for ($i = 1; $i <= $max; $i++)
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $i <= $rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.8l6.5-.9L12 3z"/></svg>
    @endfor
</div>
