<div class="flex shrink-0 flex-wrap gap-1" data-print-hidden>
    <x-ui.button variant="ghost" size="sm" icon="download" data-print title="Chọn “Lưu thành PDF” trong hộp thoại in">Tải PDF</x-ui.button>
    <x-ui.button variant="ghost" size="sm" icon="link" :data-copy="$shareUrl" data-copy-message="Đã sao chép liên kết kết quả.">Sao chép link</x-ui.button>
    <x-ui.button variant="ghost" size="sm" icon="share" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">Chia sẻ</x-ui.button>
    <x-ui.button variant="ghost" size="sm" icon="printer" data-print>In kết quả</x-ui.button>
</div>
