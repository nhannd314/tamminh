@props([
    'name' => null,
    'type' => 'text',
    'value' => null,
])

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" value="{{ old($name, $value) }}" @endif
    @if ($name && $errors->has($name)) aria-invalid="true" @endif
    {{ $attributes->class('tm-input') }}
/>
