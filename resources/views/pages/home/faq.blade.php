<section class="border-t border-cream-300 bg-cream-100 py-14 lg:py-20">
    <div class="tm-container grid items-start gap-8 lg:grid-cols-[380px_1fr] lg:gap-14">
        <div class="lg:sticky lg:top-[100px]" data-reveal>
            <x-ui.section-heading eyebrow="Hỏi & Đáp" title="Những câu hỏi" accent="thường gặp" size="sm" align="left">
                Tổng hợp 8 thắc mắc phổ biến nhất khi bạn tìm hiểu dịch vụ của Tâm Minh - từ độ chính xác của công nghệ phân tích khuôn mặt đến chi phí, bảo mật và chính sách hoàn tiền.
            </x-ui.section-heading>

            <div class="mt-8 rounded-2xl border border-cream-300 bg-white p-6 shadow-soft-sm">
                <div class="mb-2 font-serif text-xl font-semibold text-ink-600">Còn câu hỏi khác?</div>
                <p class="mb-4 text-sm leading-relaxed text-ink-300">Đội ngũ chăm sóc khách hàng của Tâm Minh sẵn sàng trả lời trong vòng 15 phút, mọi ngày trong tuần.</p>
                <x-ui.button variant="secondary" class="tracking-wide" data-toast="Mở cửa sổ liên hệ - chúng tôi sẽ phản hồi trong vòng 15 phút.">
                    <x-ui.icon name="message" :size="16" class="text-copper-400" /> Liên hệ Tâm Minh
                </x-ui.button>
            </div>
        </div>

        <x-ui.accordion data-reveal>
            @foreach ($faqs as $faq)
                <x-ui.accordion-item :question="$faq['question']" :open="$loop->first">
                    {{ $faq['answer'] }}
                </x-ui.accordion-item>
            @endforeach
        </x-ui.accordion>
    </div>
</section>
