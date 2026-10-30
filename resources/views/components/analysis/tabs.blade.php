{{--
    Các tab luận giải chi tiết (Thượng/Trung/Hạ đình, 5 giác quan…), mỗi tab gồm các khối
    luận giải và một đoạn "Tổng kết" tuỳ chọn. Chuyển tab bằng JS (data-tabs).

    Usage: <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" id="tam-dinh" />
--}}
@props([
    'tabs' => [],
    'locked' => false,
    'id',
])

<div data-tabs {{ $attributes }}>
    <div role="tablist" class="mb-6 flex flex-wrap gap-x-3 border-b border-cream-300">
        @foreach ($tabs as $tab)
            <button type="button" role="tab" id="{{ $id }}-tab-{{ $loop->index }}" aria-controls="{{ $id }}-panel-{{ $loop->index }}"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-tab="{{ $loop->index }}"
                class="-mb-px flex shrink-0 cursor-pointer flex-col items-start rounded-t-lg border-b-2 border-transparent px-4 pt-2.5 pb-3 text-left text-ink-400 transition-colors hover:text-ink-600 aria-selected:border-copper-500 aria-selected:bg-cream-100 aria-selected:text-ink-600 sm:px-5">
                <span class="text-base font-semibold">{{ $tab['title'] }}</span>
                <span class="text-xs text-ink-400">{{ $tab['subtitle'] }}</span>
            </button>
        @endforeach
    </div>

    @foreach ($tabs as $tab)
        <div role="tabpanel" id="{{ $id }}-panel-{{ $loop->index }}" aria-labelledby="{{ $id }}-tab-{{ $loop->index }}" data-tab-panel="{{ $loop->index }}"
            class="flex max-w-[860px] flex-col gap-6" @unless ($loop->first) hidden @endunless>
            <x-analysis.blocks :blocks="$tab['blocks']" :locked="$locked" />

            @isset($tab['conclusion'])
                <div class="rounded-lg bg-cream-50 p-4 text-base leading-relaxed text-ink-500 sm:p-5">
                    <div class="mb-1.5 font-semibold text-ink-600">{{ $tab['conclusion']['title'] }}</div>
                    <p @class(['tm-blurred' => $locked])>{{ $tab['conclusion']['text'] }}</p>
                </div>
            @endisset
        </div>
    @endforeach
</div>
