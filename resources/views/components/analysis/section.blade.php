{{--
    Khung một phần luận giải (Phần 1-7). Phần chưa mở khoá vẫn hiện đủ bố cục và các mục con
    như thiết kế, nhưng chữ là dữ liệu mẫu bị che mờ (partial dùng biến $locked để thêm .tm-blurred),
    kèm nút mở khoá ở đầu phần "Luận giải chi tiết".

    Usage:
    <x-analysis.section :analysis="$analysis" :item="$sections['tam_dinh']">
        …tóm tắt…
        <x-slot:detail>…luận giải chi tiết…</x-slot:detail>
    </x-analysis.section>
--}}
@props([
    'analysis',
    'item',
])

@php
    $type = $item['type'];
    $locked = ! $item['unlocked'];
@endphp

<article id="{{ $type->anchor() }}" {{ $attributes->class('scroll-mt-28 overflow-hidden rounded-xl border border-cream-300 bg-white shadow-soft-sm') }}>
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-2">
            <span class="tm-step-num">{{ $type->number() }}</span>
            <h2 class="mr-1 font-serif text-2xl font-semibold text-ink-600">{{ $type->label() }}</h2>

            @if ($locked)
                <span class="tm-tag-outline inline-flex items-center gap-1.5"><x-ui.icon name="lock" :size="12" /> Chưa mở khóa</span>
            @else
                @foreach ($item['content']['tags'] ?? [] as $tag)
                    <span class="{{ $loop->first ? 'tm-tag' : 'tm-tag-outline' }}">{{ $tag }}</span>
                @endforeach

                @if ($item['isSample'])
                    <span class="text-xs font-medium text-ink-200" title="Nội dung sẽ được thay bằng kết quả phân tích ảnh của bạn">· Dữ liệu mẫu</span>
                @endif
            @endif
        </div>

        <div @if ($locked) aria-hidden="true" @endif>
            {{ $slot }}
        </div>
    </div>

    @isset($detail)
        <div class="px-5 pb-5 sm:px-7 sm:pb-7">
            <hr class="mb-6 border-cream-300" />
            <h3 class="tm-subhead">Luận giải chi tiết</h3>

            @if ($locked)
                <x-analysis.unlock-cta :analysis="$analysis" :section="$type" :price="$item['price']" class="mb-5" />
            @endif

            <div @if ($locked) aria-hidden="true" @endif>
                {{ $detail }}
            </div>
        </div>
    @endisset
</article>
