{{-- Thông báo hoạt động realtime (Reverb): nội dung và biểu tượng do resources/js/activity-feed.js điền vào. --}}
<div data-activity-root role="status" aria-live="polite" class="pointer-events-none fixed bottom-[max(1rem,env(safe-area-inset-bottom))] left-4 z-[90] flex max-w-[calc(100vw-2rem)] -translate-x-2 translate-y-3 items-center gap-3 rounded-2xl bg-white px-4 py-3 text-sm leading-snug text-ink-600 opacity-0 shadow-soft-lg ring-1 ring-black/5 transition-all duration-300 sm:max-w-sm">
    @foreach (['analysis' => 'face', 'unlock' => 'lock', 'palm' => 'calendar', 'cart' => 'cart', 'order' => 'check', 'paid' => 'sparkle'] as $kind => $icon)
        <span data-activity-icon="{{ $kind }}" hidden class="flex size-8 shrink-0 items-center justify-center rounded-full bg-cream-100 text-copper-500">
            <x-ui.icon :name="$icon" :size="16" />
        </span>
    @endforeach
    <span data-activity-message></span>
    <button type="button" data-activity-close aria-label="Đóng" class="pointer-events-auto -mr-1 shrink-0 text-ink-400 hover:text-ink-600">&times;</button>
</div>
