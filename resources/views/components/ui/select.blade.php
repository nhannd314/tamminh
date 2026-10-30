{{--
    Usage:
    <x-ui.select name="month" placeholder="Tháng" :options="range(1, 12)" />
    <x-ui.select name="pkg" :options="['basic' => 'Cơ bản', 'pro' => 'Chuyên sâu']" />

    A list uses each item as both value and label; pass :use-keys="true"
    to use the keys of a 0-indexed array as values (e.g. [0 => '00 giờ']).
--}}
@props([
    'name' => null,
    'options' => [],
    'placeholder' => null,
    'selected' => null,
    'useKeys' => false,
])

@php
    $current = (string) ($name ? old($name, $selected) : $selected);
    $isList = ! $useKeys && array_is_list($options);
@endphp

<select @if ($name) name="{{ $name }}" @endif {{ $attributes->class('tm-select') }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $value => $label)
        @php($optionValue = (string) ($isList ? $label : $value))
        <option value="{{ $optionValue }}" @selected($current !== '' && $current === $optionValue)>{{ $label }}</option>
    @endforeach

    {{ $slot }}
</select>
