{{--
    Selectable option card.
    Usage: <x-ui.radio-card name="package" value="basic" title="Cơ bản" description="Miễn phí" highlight checked />
--}}
@props([
    'name',
    'value',
    'title',
    'description' => null,
    'highlight' => false,
    'checked' => false,
])

<label class="tm-radio-card">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" class="sr-only" @checked(old($name) !== null ? old($name) === (string) $value : $checked) {{ $attributes }} />
    <span class="tm-radio-dot mt-px"></span>
    <span>
        <span class="block text-sm font-semibold text-ink-600">{{ $title }}</span>
        @if ($description)
            <span @class(['mt-0.5 block text-xs', 'text-copper-600' => $highlight, 'text-ink-300' => ! $highlight])>{{ $description }}</span>
        @endif
    </span>
</label>
