{{--
    Ảnh cắt một bộ phận (lông mày, mắt, tai, mũi, miệng) từ ảnh khuôn mặt đã cắt sẵn.
    `box` = [x, y, w, h, tỉ lệ rộng/cao]: vị trí tính theo % ảnh cắt, do FaceDetector đo bằng MediaPipe.

    Usage: <x-analysis.feature-crop :analysis="$analysis" :box="$analysis->face_metrics['features']['mat']" label="Mắt" class="w-56" />
--}}
@props([
    'analysis',
    'box',
    'label' => '',
])

@php
    [$x, $y, $w, $h, $aspect] = $box;
    $style = sprintf(
        'aspect-ratio: %s; background-image: url(%s); background-size: %s%% %s%%; background-position: %s%% %s%%;',
        $aspect,
        route('analyses.face', $analysis),
        round(10000 / $w, 2),
        round(10000 / $h, 2),
        $w >= 100 ? 50 : round($x / (100 - $w) * 100, 2),
        $h >= 100 ? 50 : round($y / (100 - $h) * 100, 2),
    );
@endphp

<div role="img" aria-label="{{ $label }} của {{ $analysis->name }}" style="{{ $style }}"
    {{ $attributes->class('shrink-0 overflow-hidden rounded-xl border border-cream-300 bg-cream-200 bg-no-repeat') }}></div>
