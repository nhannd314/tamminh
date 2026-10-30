@props([
    'name' => null,
    'type' => 'text',
])

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" value="{{ old($name) }}" @endif
    @if ($name && $errors->has($name)) aria-invalid="true" @endif
    {{ $attributes->class('tm-input') }}
/>
