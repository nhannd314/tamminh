{{--
    Giờ / Phút as two selects.
    Usage: <x-ui.time-select name="birth" /> → birth_hour, birth_minute
--}}
@props([
    'name' => 'birth',
    'minuteStep' => 1,
])

@php
    $hours = collect(range(0, 23))->mapWithKeys(fn (int $hour): array => [$hour => str_pad($hour, 2, '0', STR_PAD_LEFT).' giờ'])->all();
    $minutes = collect(range(0, 59, $minuteStep))->mapWithKeys(fn (int $minute): array => [$minute => str_pad($minute, 2, '0', STR_PAD_LEFT).' phút'])->all();
@endphp

<div {{ $attributes->class('grid grid-cols-2 gap-2.5') }}>
    <x-ui.select :name="$name.'_hour'" placeholder="Giờ" :options="$hours" :use-keys="true" aria-label="Giờ sinh" />
    <x-ui.select :name="$name.'_minute'" placeholder="Phút" :options="$minutes" :use-keys="true" aria-label="Phút sinh" />
</div>
