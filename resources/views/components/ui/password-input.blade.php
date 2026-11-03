{{--
    Ô mật khẩu có nút hiện/ẩn; `strength` thêm thước đo độ mạnh (Yếu → Mạnh) cập nhật khi gõ.
    Usage: <x-ui.password-input id="password" name="password" autocomplete="new-password" strength />
--}}
@props([
    'name',
    'strength' => false,
])

<div class="flex flex-col gap-2" data-password-field>
    <div class="relative">
        <input
            type="password"
            name="{{ $name }}"
            @if ($errors->has($name)) aria-invalid="true" @endif
            data-password-input
            {{ $attributes->class('tm-input pr-11') }}
        />
        <button type="button" data-password-toggle aria-label="Hiện mật khẩu"
            class="absolute top-1/2 right-2 flex size-8 -translate-y-1/2 cursor-pointer items-center justify-center rounded text-ink-200 transition-colors hover:text-ink-400 aria-pressed:text-copper-600">
            <x-ui.icon name="eye" :size="18" data-password-icon="show" />
            <x-ui.icon name="eye-off" :size="18" data-password-icon="hide" class="hidden" />
        </button>
    </div>

    @if ($strength)
        <div class="flex items-center gap-3" data-password-strength aria-live="polite">
            <div class="grid flex-1 grid-cols-4 gap-1.5">
                @foreach (range(1, 4) as $bar)
                    <span class="h-1 rounded-full bg-cream-200 transition-colors duration-250" data-strength-bar></span>
                @endforeach
            </div>
            <span class="w-16 text-right text-xs font-medium" data-strength-label></span>
        </div>
    @endif
</div>
