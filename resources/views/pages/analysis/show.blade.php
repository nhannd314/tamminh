<x-layouts.app title="Kết quả luận giải" description="Kết quả luận giải nhân tướng học cá nhân hóa tại Tâm Minh.">
    @push('head')
        <meta name="robots" content="noindex, nofollow">
        <meta property="og:title" content="Kết quả luận giải tướng số · Tâm Minh">
        <meta property="og:description" content="Khám phá bản đồ nhân tướng học của bạn tại Tâm Minh.">
        <meta property="og:url" content="{{ route('analyses.show', $analysis) }}">
        <meta property="og:image" content="{{ asset('images/hero-mountains.webp') }}">
    @endpush

    @php
        $shareUrl = route('analyses.show', $analysis);
    @endphp

    <div class="tm-container pt-6 pb-16">
        <nav aria-label="Breadcrumb" class="mb-6 flex flex-wrap items-center gap-1.5 text-sm text-ink-300" data-print-hidden>
            <a href="{{ route('home') }}" class="hover:text-copper-600">Trang chủ</a>
            <x-ui.icon name="chevron-down" :size="14" class="-rotate-90" />
            <span>Kết quả luận giải</span>
            <x-ui.icon name="chevron-down" :size="14" class="-rotate-90" />
            <span class="font-medium text-ink-600">{{ $analysis->name }}</span>
        </nav>

        <div class="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-8">
            @include('pages.analysis.sidebar')

            <div class="flex min-w-0 flex-col gap-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="font-serif text-2xl font-semibold text-ink-600 sm:text-3xl">Kết quả luận giải tướng số</h1>
                            @if ($analysis->isFullyUnlocked())
                                <span class="rounded-full border border-success/30 bg-success/10 px-3 py-1 text-sm font-semibold text-success">Đã mở khóa toàn bộ</span>
                            @endif
                        </div>
                        <p class="mt-2 text-base text-ink-300">Luận giải chuyên sâu dựa trên nhân tướng học và vận mệnh</p>
                    </div>

                    @include('pages.analysis.actions')
                </div>

                @include('pages.analysis.overview')
                @include('pages.analysis.sections.ngu-hanh', ['item' => $sections['ngu_hanh']])
                @include('pages.analysis.sections.than-khi-sac', ['item' => $sections['than_khi_sac']])
                @include('pages.analysis.sections.tam-dinh', ['item' => $sections['tam_dinh']])
                @include('pages.analysis.sections.ngu-quan', ['item' => $sections['ngu_quan']])
                @include('pages.analysis.sections.thap-nhi-cung', ['item' => $sections['thap_nhi_cung']])
                @include('pages.analysis.sections.phong-thuy', ['item' => $sections['phong_thuy']])
                @unless ($analysis->isFullyUnlocked())
                    <form method="POST" action="{{ route('analyses.unlock', $analysis) }}" data-reveal data-print-hidden
                        class="flex flex-col items-center gap-5 rounded-xl bg-navy-500 p-6 text-center sm:flex-row sm:justify-between sm:p-8 sm:text-left">
                        @csrf
                        <div class="min-w-0">
                            <div class="text-sm font-semibold tracking-wider text-copper-300 uppercase">Mở khóa toàn bộ</div>
                            <h3 class="mt-1.5 font-serif text-2xl font-semibold text-cream-50 sm:text-3xl">
                                Chỉ <span class="text-copper-300">{{ intdiv($fullPackage['amount'], 1000) }}k</span> mở khóa toàn bộ luận giải
                            </h3>
                            <p class="mt-2 text-base text-cream-50/80">
                                Đọc trọn {{ count($analysis->lockedSections()) }} phần còn lại, tải PDF và lưu trữ trọn đời.
                                @if ($fullPackage['discount'] > 0)
                                    <span class="whitespace-nowrap text-copper-200">Đã giảm <x-analysis.price :amount="$fullPackage['discount']" /> nhờ các phần bạn đã mua.</span>
                                @endif
                            </p>
                        </div>
                        <x-ui.button type="submit" size="lg" class="shrink-0">
                            Mở khóa ngay · <x-analysis.price :amount="$fullPackage['amount']" />
                        </x-ui.button>
                    </form>
                @endunless

                @include('pages.analysis.sections.advice', ['item' => $sections['advice']])
            </div>
        </div>

        <div class="mt-4 flex justify-center sm:justify-end">
            @include('pages.analysis.actions')
        </div>

        @include('pages.analysis.products')
    </div>

    @if ($payment)
        @push('modals')
            @include('pages.analysis.payment', ['payment' => $payment])
        @endpush
    @endif
</x-layouts.app>
