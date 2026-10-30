<section class="border-t border-cream-300 bg-cream-100 py-14 lg:py-20">
    <div class="tm-container">
        <x-ui.section-heading eyebrow="Cảm nhận của khách hàng" title="Tâm Minh được kể lại" accent="bằng câu chuyện riêng của từng người." size="md" class="mb-10 max-w-[880px] gap-5" data-reveal>
            Hơn 8.500 lá số đã được an chuẩn xác và luận giải bằng tay. Đây là những lời chia sẻ thật từ khách hàng - không chỉnh sửa, không kịch bản.
        </x-ui.section-heading>

        <dl class="mb-10 grid grid-cols-2 rounded-2xl border border-cream-300 bg-white shadow-soft-sm lg:grid-cols-4 lg:py-7" data-reveal>
            @foreach ($stats as $stat)
                <div @class([
                    'flex flex-col-reverse items-center justify-end gap-1.5 px-4 py-5 text-center lg:px-7 lg:py-0',
                    'border-cream-300',
                    'border-r' => $loop->odd,
                    'lg:border-r' => ! $loop->last,
                    'border-t lg:border-t-0' => $loop->index >= 2,
                ])>
                    <dt class="max-w-[180px] text-xs leading-snug text-ink-300">{{ $stat['label'] }}</dt>
                    <dd class="font-serif text-3xl leading-none font-semibold tracking-[-0.02em] text-copper-700 lg:text-4xl">{{ $stat['value'] }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5" data-reveal-stagger>
            @foreach ($reviews as $review)
                <x-cards.review :review="$review" data-reveal />
            @endforeach
        </div>
    </div>
</section>
