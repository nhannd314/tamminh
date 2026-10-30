{{--
    Ngày / Tháng / Năm sinh as three selects.
    Usage: <x-ui.date-select name="birth" /> → birth_day, birth_month, birth_year
--}}
@props([
    'name' => 'birth',
    'fromYear' => 2010,
    'years' => 80,
])

<div {{ $attributes->class('grid grid-cols-3 gap-2.5') }}>
    <x-ui.select :name="$name.'_day'" placeholder="Ngày" :options="range(1, 31)" aria-label="Ngày sinh" />
    <x-ui.select :name="$name.'_month'" placeholder="Tháng" :options="range(1, 12)" aria-label="Tháng sinh" />
    <x-ui.select :name="$name.'_year'" placeholder="Năm" :options="range($fromYear, $fromYear - $years + 1)" aria-label="Năm sinh" />
</div>
