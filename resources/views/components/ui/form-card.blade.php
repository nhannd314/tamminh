{{--
    White elevated container with an uppercase header, used for lead forms.
    Usage: <x-ui.form-card title="Nhập thông tin của bạn">…</x-ui.form-card>
--}}
@props([
    'title',
])

<div {{ $attributes->class('tm-form') }}>
    <h3 class="tm-form-header">{{ $title }}</h3>
    {{ $slot }}
</div>
