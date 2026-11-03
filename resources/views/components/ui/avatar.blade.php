{{-- Initial avatar. tone: copper | navy · size: sm (28px) | md (40px) | lg (44px) | xl (56px) --}}
@props([
    'tone' => 'copper',
    'size' => 'lg',
])

<span {{ $attributes->class([
    'flex shrink-0 items-center justify-center rounded-full font-sans font-semibold',
    match ($size) {
        'sm' => 'size-7 text-sm',
        'md' => 'size-10 text-base',
        'xl' => 'size-14 text-2xl',
        default => 'size-11 text-lg',
    },
    'border border-copper-200 bg-copper-50 text-copper-700' => $tone === 'copper',
    'bg-navy-500 text-copper-400' => $tone === 'navy',
]) }} aria-hidden="true">{{ $slot }}</span>
