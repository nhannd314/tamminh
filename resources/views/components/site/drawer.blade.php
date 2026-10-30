<div data-drawer-scrim class="pointer-events-none fixed inset-0 z-[60] bg-ink-600/45 opacity-0 transition-opacity duration-300 lg:hidden"></div>

<aside data-drawer class="fixed inset-y-0 right-0 z-[70] flex w-[min(340px,88vw)] translate-x-full flex-col overflow-y-auto bg-white shadow-soft-lg transition-transform duration-300 ease-out-expo lg:hidden" aria-label="Menu di động">
    <div class="flex items-center justify-between border-b border-cream-300 px-6 py-5">
        <span class="tm-overline text-copper-700">Menu</span>
        <button type="button" data-drawer-close aria-label="Đóng menu" class="flex size-8 cursor-pointer items-center justify-center rounded-md text-ink-300 hover:bg-cream-100 hover:text-ink-600">
            <x-ui.icon name="x" :size="20" />
        </button>
    </div>

    <nav class="flex-1 py-3">
        <x-site.nav-links variant="drawer" />
    </nav>

    <div class="flex flex-col gap-3 border-t border-cream-300 px-6 pt-4 pb-[max(1.5rem,env(safe-area-inset-bottom))]">
        <x-ui.button href="#" size="sm" icon="sparkle" :icon-size="14" class="w-max" data-toast="Mở chương trình affiliate.">Kiếm tiền affiliate</x-ui.button>
        <x-ui.button variant="secondary" class="gap-2.5 tracking-wide" data-toast="Mở cửa sổ đăng nhập…">
            <x-ui.icon name="user" :size="18" class="text-copper-400" /> Đăng nhập
        </x-ui.button>
    </div>
</aside>
