{{-- Initial avatar. tone: copper | navy --}}
@props([
    'tone' => 'copper',
])

<span {{ $attributes->class([
    'flex size-11 shrink-0 items-center justify-center rounded-full font-serif text-lg font-semibold',
    'border border-copper-200 bg-copper-50 text-copper-700' => $tone === 'copper',
    'bg-navy-500 text-copper-400' => $tone === 'navy',
]) }} aria-hidden="true">{{ $slot }}</span>
