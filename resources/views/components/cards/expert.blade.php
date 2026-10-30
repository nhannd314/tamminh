@props([
    'expert',
])

{{-- @var array{glyph: string, name: string, role: string, years: int, sessions: int, photo: ?string} $expert --}}
<article {{ $attributes->class('flex items-stretch gap-4 rounded-[10px] border border-cream-300 bg-white p-4 sm:flex-col sm:p-5 xl:flex-row xl:gap-5 shadow-soft-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-copper-200 hover:shadow-soft-md') }}>
    @if ($expert['photo'])
        <img src="{{ $expert['photo'] }}" alt="{{ $expert['name'] }}" loading="lazy" class="size-28 shrink-0 rounded-lg bg-cream-200 object-cover object-top min-[400px]:size-36 sm:h-52 sm:w-full xl:size-40" />
    @else
        <x-ui.glyph tone="navy" size="xl">{{ $expert['glyph'] }}</x-ui.glyph>
    @endif

    <div class="flex min-w-0 flex-1 flex-col">
        <h3 class="mb-1 text-lg leading-tight font-semibold text-ink-600 sm:text-xl">{{ $expert['name'] }}</h3>
        <div class="text-sm text-ink-300">{{ $expert['role'] }}</div>
        <div class="mt-2.5 mb-4 flex flex-col gap-1 text-sm text-ink-500">
            <span>Kinh nghiệm: <b class="font-medium">{{ $expert['years'] }} năm</b></span>
            <span>Lượt tư vấn: <b class="font-medium">{{ number_format($expert['sessions'], 0, ',', '.') }}</b></span>
        </div>
        <x-ui.button
            block
            icon="messenger"
            class="mt-auto py-2.5 uppercase tracking-wide"
            data-modal-open="expert"
            :data-modal-payload="json_encode(['glyph' => $expert['glyph'], 'name' => $expert['name'], 'role' => $expert['role']])"
        >Kết nối</x-ui.button>
    </div>
</article>
