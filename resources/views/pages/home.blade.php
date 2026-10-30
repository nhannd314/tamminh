<x-layouts.app title="Tướng số & Tử vi">
    @include('pages.home.hero')
    @include('pages.home.analysis')
    @include('pages.home.experts')
    @include('pages.home.palm-reading')
    @include('pages.home.reviews')
    @include('pages.home.blog')
    @include('pages.home.faq')
    @include('pages.home.trust')

    @push('modals')
        <x-ui.modal id="expert">
            <x-slot:header>
                <x-ui.glyph tone="navy" size="md" class="rounded-md" data-modal-field="glyph"></x-ui.glyph>
                <div>
                    <div class="text-base font-semibold text-ink-600" data-modal-field="name"></div>
                    <div class="text-xs text-ink-300" data-modal-field="role"></div>
                </div>
            </x-slot:header>

            <p class="mb-4 text-sm leading-relaxed text-ink-500">
                Cảm ơn bạn đã quan tâm. <span data-modal-field="name"></span> sẽ liên hệ với bạn trong thời gian sớm nhất. Trong khi chờ đợi, bạn có thể để lại câu hỏi.
            </p>
            <form data-toast-on-submit="Đã gửi tin nhắn - Thầy sẽ phản hồi trong thời gian sớm nhất.">
                <x-ui.input name="question" placeholder="Viết câu hỏi của bạn…" aria-label="Câu hỏi" required />
                <div class="mt-4 flex justify-end">
                    <x-ui.button type="submit" trailing-icon="send" :icon-size="14" class="uppercase tracking-wide">Gửi</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endpush
</x-layouts.app>
