{{--
    Inline radio with custom dot.
    Usage: <x-ui.radio name="gender" value="male" checked>Nam</x-ui.radio>
--}}
@props([
    'name',
    'value',
    'checked' => false,
])

<label class="tm-radio">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="sr-only" @checked(old($name) !== null ? old($name) === (string) $value : $checked) {{ $attributes }} />
    <span class="tm-radio-dot"></span>
    {{ $slot }}
</label>
