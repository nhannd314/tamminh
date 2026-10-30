<header data-sticky-header class="sticky top-0 z-50 border-b border-cream-300 bg-white/95 backdrop-blur transition-shadow duration-300">
    <div class="tm-container flex items-center gap-5 py-3.5">
        <x-site.logo />

        <nav class="hidden flex-1 justify-center gap-[18px] lg:flex" aria-label="Điều hướng chính">
            <x-site.nav-links />
        </nav>

        <x-ui.button href="#" size="sm" icon="sparkle" :icon-size="14" class="ml-auto hidden sm:inline-flex lg:ml-0" data-toast="Mở chương trình affiliate - đăng ký nhận hoa hồng mỗi lượt giới thiệu.">
            <span class="hidden xl:inline">Kiếm tiền affiliate</span>
        </x-ui.button>

        <x-ui.button variant="soft" size="icon" class="hidden lg:inline-flex" aria-label="Đăng nhập" title="Đăng nhập" data-toast="Mở cửa sổ đăng nhập…">
            <x-ui.icon name="user" :size="20" />
        </x-ui.button>

        <button type="button" data-burger aria-label="Mở menu" aria-expanded="false" class="ml-auto flex size-10 shrink-0 cursor-pointer flex-col items-center justify-center gap-[5px] rounded-md border border-cream-300 hover:border-copper-400 sm:ml-0 lg:hidden">
            <span class="block h-[1.5px] w-[18px] rounded bg-ink-600 transition-transform duration-300"></span>
            <span class="block h-[1.5px] w-[18px] rounded bg-ink-600 transition-opacity duration-300"></span>
            <span class="block h-[1.5px] w-[18px] rounded bg-ink-600 transition-transform duration-300"></span>
        </button>
    </div>
</header>
