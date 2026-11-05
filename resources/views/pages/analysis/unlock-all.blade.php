@if ($analysis->purchasableSections() !== [])
    <form method="POST" action="{{ route('analyses.unlock', $analysis) }}" data-unlock-form data-reveal data-print-hidden
        class="flex flex-col items-center gap-5 rounded-xl bg-navy-500 p-6 text-center sm:flex-row sm:justify-between sm:p-8 sm:text-left">
        @csrf
        <div class="min-w-0">
            <div class="text-sm font-semibold tracking-wider text-copper-300 uppercase">Mở khóa toàn bộ</div>
            <h3 class="mt-1.5 font-serif text-2xl font-semibold text-cream-50 sm:text-3xl">
                Chỉ <span class="text-copper-300">{{ intdiv($fullPackage['amount'], 1000) }}k</span> mở khóa toàn bộ luận giải
            </h3>
            <p class="mt-2 text-base text-cream-50/80">
                Đọc trọn {{ count($analysis->purchasableSections()) }} phần còn lại, tải PDF và lưu trữ trọn đời.
                @if ($fullPackageDiscountPercent > 0)
                    <span class="whitespace-nowrap text-copper-200">Giảm {{ $fullPackageDiscountPercent }}% so với mua lẻ{{ $hasBoughtSections ? ', đã trừ các phần bạn đã mua' : '' }}.</span>
                @endif
            </p>
        </div>
        <x-ui.button type="submit" size="lg" class="shrink-0">
            Mở khóa ngay · <x-analysis.price :amount="$fullPackage['amount']" />
        </x-ui.button>
    </form>
@endif
