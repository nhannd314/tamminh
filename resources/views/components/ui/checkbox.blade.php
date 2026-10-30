{{--
    Inline checkbox with custom box.
    Usage: <x-ui.checkbox name="birth_time_unknown">Không rõ giờ sinh</x-ui.checkbox>
--}}
@props([
    'name',
    'value' => '1',
    'checked' => false,
])

<label class="tm-checkbox">
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" class="sr-only" @checked(old($name) !== null ? old($name) === (string) $value : $checked) {{ $attributes }} />
    <span class="tm-checkbox-box"></span>
    {{ $slot }}
</label>
