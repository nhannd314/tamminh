{{--
    Usage:
    <x-ui.modal id="expert">
        <x-slot:header>…</x-slot:header>
        …
    </x-ui.modal>

    Open with any element carrying data-modal-open="expert" and an optional
    data-modal-payload='{"name":"…"}' — values fill [data-modal-field="name"].
--}}
@props([
    'id',
])

<div data-modal="{{ $id }}" role="dialog" aria-modal="true" class="fixed inset-0 z-[200] hidden items-center justify-center bg-ink-600/45 p-4">
    <div {{ $attributes->class('w-full max-w-[520px] overflow-hidden rounded-2xl bg-white shadow-soft-lg') }}>
        @isset($header)
            <div class="flex items-center gap-3.5 border-b border-cream-300 px-6 py-4">
                <div class="flex flex-1 items-center gap-3.5">{{ $header }}</div>
                <button type="button" data-modal-close aria-label="Đóng" class="cursor-pointer text-ink-300 transition-colors hover:text-ink-600">
                    <x-ui.icon name="x" :size="18" />
                </button>
            </div>
        @endisset

        <div class="p-6">{{ $slot }}</div>
    </div>
</div>
