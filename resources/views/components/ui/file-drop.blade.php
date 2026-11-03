{{--
    Ô chọn tệp kéo-thả. Chọn ảnh thì hiện ảnh xem trước ngay trong khung.
    Form gửi ảnh trước (data-portrait-upload) đặt `data-state` lên khung: scanning (hiệu ứng quét) → detected (tick xanh).
    Usage: <x-ui.file-drop name="portrait" accept="image/*" hint="(Ảnh rõ mặt)" />
--}}
@props([
    'name',
    'accept' => 'image/*',
    'title' => 'Kéo thả ảnh vào đây hoặc click để chọn ảnh',
    'hint' => null,
])

<label class="tm-dropzone" data-dropzone>
    <input type="file" name="{{ $name }}" accept="{{ $accept }}" class="sr-only" data-file-input {{ $attributes }} />

    <span class="tm-dropzone-empty">
        <x-ui.icon name="upload" :size="32" class="mx-auto mb-2 text-copper-400" />
        <span class="mb-0.5 block font-medium text-ink-500" data-file-name>{{ $title }}</span>
        @if ($hint)
            <span class="block">{{ $hint }}</span>
        @endif
    </span>

    <span class="tm-dropzone-preview" aria-hidden="true">
        <img alt="" class="size-full object-contain" data-file-preview />
        <span class="tm-scan-grid"></span>
        <span class="tm-scan-line"></span>
        <span class="tm-dropzone-change">Đổi ảnh</span>
    </span>

    <span class="tm-dropzone-status" role="status" aria-live="polite">
        <span class="tm-dropzone-spinner"></span>
        <x-ui.icon name="check" :size="14" class="tm-dropzone-check" />
        <span data-file-status></span>
    </span>
</label>
