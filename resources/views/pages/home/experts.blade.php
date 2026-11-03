<section class="bg-cream-50 pb-16 lg:pb-20">
    <div class="tm-container">
        <div class="mb-7" data-reveal>
            <h2 class="text-lg font-semibold tracking-[0.1em] text-ink-600 uppercase sm:text-center sm:text-2xl">Xem trực tiếp với thầy tướng số</h2>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:gap-5" data-reveal-stagger>
            @foreach ($experts as $expert)
                <x-cards.expert :expert="$expert" data-reveal />
            @endforeach
        </div>
    </div>
</section>
