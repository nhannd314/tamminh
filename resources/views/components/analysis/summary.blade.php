{{--
    Đoạn tóm tắt của một phần; `text` là một chuỗi hoặc danh sách đoạn văn.
    Usage: <x-analysis.summary :text="$content['summary']" :locked="$locked" />
--}}
@props([
    'text',
    'locked' => false,
])

<div {{ $attributes->class('flex max-w-[860px] flex-col gap-3 text-base leading-relaxed text-ink-500') }}>
    @foreach ((array) $text as $paragraph)
        <p @class(['tm-blurred' => $locked])>{{ $paragraph }}</p>
    @endforeach
</div>
