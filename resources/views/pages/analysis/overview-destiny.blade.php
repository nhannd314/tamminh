{{-- Đoạn luận bản mệnh do Gemini viết; luồng stream chỉ thay khối này để hiệu ứng của phần Bát tự không bị cắt ngang. --}}
@php
    $destiny = $chart->destinyElement();
    $item = $sections['overview'];
    $intro = '<b class="font-semibold text-copper-700">'.e($chart->destiny()).'</b> - mệnh '.e($destiny->label()).', được '.e($destiny->generatedBy()->label()).' sinh và sinh ra '.e($destiny->generates()->label()).'.';
    $paragraphs = $item['content']['destiny'] ?: [''];
@endphp

<div id="{{ \App\AnalysisSection::Overview->anchor() }}-destiny" class="space-y-3 text-[17px] text-ink-500">
    @foreach ($paragraphs as $paragraph)
        <p>
            @if ($loop->first)
                {!! $intro !!}
            @endif
            {{ $paragraph }}
        </p>
    @endforeach

    @if ($item['loading'])
        <x-analysis.loading label="Đang luận giải bản mệnh…" />
    @endif
</div>
