{{--
    Label + control + validation error wrapper.
    Usage:
    <x-ui.field label="Họ và tên" for="name" name="name">
        <x-ui.input id="name" name="name" />
    </x-ui.field>
--}}
@props([
    'label' => null,
    'for' => null,
    'name' => null,
])

<div {{ $attributes->class('tm-field') }}>
    @if ($label)
        @if ($for)
            <label for="{{ $for }}" class="tm-label">{{ $label }}</label>
        @else
            <span class="tm-label">{{ $label }}</span>
        @endif
    @endif

    {{ $slot }}

    @if ($name)
        @error($name)
            <p class="tm-error">{{ $message }}</p>
        @enderror
    @endif
</div>
