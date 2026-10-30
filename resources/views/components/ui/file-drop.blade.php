@props([
    'name',
    'accept' => 'image/*',
    'title' => 'Kéo thả ảnh vào đây hoặc click để chọn ảnh',
    'hint' => null,
])

<label class="tm-dropzone">
    <input type="file" name="{{ $name }}" accept="{{ $accept }}" class="sr-only" data-file-input {{ $attributes }} />
    <x-ui.icon name="upload" :size="32" class="mx-auto mb-2 text-copper-400" />
    <span class="mb-0.5 block font-medium text-ink-500" data-file-name>{{ $title }}</span>
    @if ($hint)
        <span class="block">{{ $hint }}</span>
    @endif
</label>
