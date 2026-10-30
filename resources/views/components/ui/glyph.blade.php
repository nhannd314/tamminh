{{--
    Hán tự tile.
    tone: copper | navy | ring (round, cream fill)
    size: sm (40px) | md (48px) | lg (60–72px) | xl (144–160px)
--}}
@props([
    'tone' => 'copper',
    'size' => 'sm',
])

@php
    $sizeClasses = match ($size) {
        'md' => 'size-12 text-2xl',
        'lg' => 'size-[60px] text-3xl lg:size-[72px] lg:text-4xl',
        'xl' => 'size-28 text-4xl min-[400px]:size-36 min-[400px]:text-5xl sm:h-40 sm:w-full xl:size-40',
        default => 'size-10 text-xl',
    };

    $toneClasses = match ($tone) {
        'navy' => 'tm-glyph-navy',
        'ring' => 'flex shrink-0 items-center justify-center rounded-full border-[1.5px] border-copper-400 bg-cream-100 font-serif font-medium text-copper-700',
        default => 'tm-glyph',
    };
@endphp

<span {{ $attributes->class([$toneClasses, $sizeClasses]) }} aria-hidden="true">{{ $slot }}</span>
