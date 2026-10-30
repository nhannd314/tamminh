<div data-toast-root @if (session('toast')) data-flash="{{ session('toast') }}" @endif role="status" aria-live="polite" class="pointer-events-none fixed bottom-[max(2rem,env(safe-area-inset-bottom))] left-1/2 z-[100] flex w-max max-w-[calc(100vw-2rem)] -translate-x-1/2 translate-y-3 items-center gap-2 rounded-full bg-navy-500 px-5 py-3 text-sm leading-snug text-white opacity-0 shadow-soft-lg transition-all duration-300">
    <x-ui.icon name="sparkle" :size="16" class="text-copper-400" />
    <span data-toast-message></span>
</div>
