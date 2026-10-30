{{--
    Ảnh chân dung cắt sát khuôn mặt.
    Có ảnh cắt sẵn (FaceDetector) thì dùng ảnh đó; `tight` phóng to thêm để khuôn mặt
    (chân tóc → cằm) lấp gần đầy khung, hợp với ảnh tròn nhỏ.
    Chưa có số đo thì cắt bằng CSS theo `box` = [x, y, w, h] tính theo % ảnh gốc.

    Usage: <x-analysis.face-crop :analysis="$analysis" tight class="size-32 rounded-full" />
--}}
@props([
    'analysis',
    'box' => null,
    'tight' => false,
])

@php
    $lines = $analysis->face_metrics['lines'] ?? null;

    if ($analysis->face_crop_path && $lines) {
        $url = route('analyses.face', $analysis);
        $faceHeight = max(10, $lines[3] - $lines[0]);
        $size = $tight ? max(100, round(100 * 100 / ($faceHeight * 1.2), 2)) : 100;
        $centerY = ($lines[0] + $lines[3]) / 2;
        $positionY = $size > 100 ? round(min(100, max(0, ($centerY * $size / 100 - 50) / ($size - 100) * 100)), 2) : 50;
    } else {
        $url = route('analyses.portrait', $analysis);
        [$x, $y, $w, $h] = $box ?? [22, 8, 56, 56];
        $size = round(10000 / $h, 2);
        $positionY = $h >= 100 ? 50 : round($y / (100 - $h) * 100, 2);
    }

    $style = $analysis->face_crop_path && $lines
        ? sprintf('background-image: url(%s); background-size: %s%%; background-position: 50%% %s%%;', $url, $size, $positionY)
        : sprintf('background-image: url(%s); background-size: %s%% %s%%; background-position: %s%% %s%%;', $url, round(10000 / $w, 2), $size, $w >= 100 ? 50 : round($x / (100 - $w) * 100, 2), $positionY);
@endphp

<div role="img" aria-label="Khuôn mặt của {{ $analysis->name }}" style="{{ $style }}" {{ $attributes->class('shrink-0 bg-cream-200 bg-no-repeat') }}></div>
