<section class="bg-cream-50 pb-16 lg:pb-20">
    <div class="tm-container">
        <div class="mb-7 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between" data-reveal>
            <div class="hidden flex-1 sm:block"></div>
            <h2 class="text-lg font-semibold tracking-[0.1em] text-ink-600 uppercase sm:text-center sm:text-2xl">Xem trực tiếp với thầy tướng số</h2>
            <div class="flex-1 sm:text-right">
                <a href="#" class="tm-link inline-block py-2 text-sm" data-toast="Mở danh sách chuyên gia.">Xem tất cả chuyên gia →</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:gap-5" data-reveal-stagger>
            @foreach ($experts as $expert)
                <x-cards.expert :expert="$expert" data-reveal />
            @endforeach
        </div>
    </div>
</section>
