@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];

    // Số đo thật từ ảnh (FaceDetector) luôn hiển thị; chưa đo được thì dùng số Gemini ước lượng (hoặc số mẫu khi còn khoá).
    $measured = $analysis->face_metrics;
    $lines = $measured['lines'] ?? [24, 42, 63, 82];
    $ratios = $measured['ratios'] ?? $content['ratios'];
    $blurRatios = $locked && ! $measured;

    $zones = collect([['Thượng đình', '15–30 tuổi'], ['Trung đình', '31–50 tuổi'], ['Hạ đình', '51+ tuổi']])
        ->map(fn (array $zone, int $index): array => [...$zone, ($lines[$index] + $lines[$index + 1]) / 2, $ratios[$index]]);
    $largest = max($ratios);

    $zoneIntros = [
        'Thượng đình là phần khuôn mặt từ đường chân tóc xuống đến ấn đường (giữa hai đầu lông mày), gồm vùng trán và hai bên thái dương. Trong tướng học, đây là "Thiên thời" — phần trời ban — phản ánh trí tuệ, dòng tộc và tiền vận của đời người, từ lúc sinh ra đến khoảng 30 tuổi. Vùng này cho biết hoàn cảnh xuất thân, sự nuôi dưỡng của gia đình và con đường học vấn, sự nghiệp ban đầu. Trán cao, rộng, đầy đặn, da sáng và chân tóc gọn gàng là dấu hiệu thông minh, tư duy tốt, gia đạo hòa thuận và dễ được quý nhân nâng đỡ từ sớm. Ngược lại, trán hẹp, vát, có sẹo hoặc chân tóc lấn sâu thường báo hiệu thời trẻ nhiều va vấp, phải tự lập sớm và nỗ lực gấp đôi mới thành tựu. Hai bên trán còn là nơi xem duyên phận với cha mẹ và tổ tiên. Thượng đình chỉ thực sự trọn vẹn khi hài hòa với trung đình và hạ đình, nên cần đọc cùng hai phần còn lại.',
        'Trung đình là phần trung tâm khuôn mặt, từ ngang ấn đường (giữa hai đầu lông mày) xuống chóp mũi, gồm lông mày, mắt, mũi và gò má. Đây là "Nhân hòa" — phần nỗ lực của bản thân — phản ánh sự nghiệp chính, khả năng hành động, tài lộc và gia đạo trong trung vận, từ 31 đến 50 tuổi. Mũi quản lý tiền tài (khả năng kiếm và giữ tiền), gò má thể hiện ý chí phấn đấu và uy quyền lãnh đạo. Lông mày thanh tú, mắt có thần, sống mũi thẳng, chóp mũi đầy và gò má nhô vừa phải là dấu hiệu ý chí kiên cường, tự tay làm nên cơ nghiệp và có uy tín. Ngược lại, sống mũi gãy lệch, mắt lừ đừ hoặc gò má trơ xương thường báo hiệu làm ăn dễ vấp ngã, tiền vào lại ra, tình cảm vợ chồng dễ rạn nứt. Thượng đình dù thông minh đến đâu mà trung đình yếu thì cũng khó biến ý tưởng thành thành tựu.',
        'Hạ đình là phần đáy khuôn mặt, từ chân mũi xuống hết cằm, gồm nhân trung, miệng, pháp lệnh, má dưới và cằm. Đây là "Địa lợi" — phần tích lũy — phản ánh hậu vận từ 51 tuổi trở đi: sức khỏe tuổi già, tài sản, nhà cửa, con cháu và sự an yên về sau. Cằm (địa các) tượng trưng cho đất đai, tài sản tích lũy và khả năng quy tụ người giúp sức, còn nhân trung và miệng cho thấy tình cảm vợ chồng, sự hiếu thảo của con cái và tuổi thọ. Nhân trung sâu rõ, miệng vuông đặn, môi hồng và cằm tròn đầy là dấu hiệu tuổi già an nhàn, tài sản vững, con cháu sum vầy. Ngược lại, cằm lẹm hoặc nhọn, nhân trung nông lệch, môi mỏng và khóe miệng xệ thường báo hiệu về già cô độc, tiền bạc phân tán, thiếu chỗ dựa lúc ốm đau. Hạ đình vững vàng có thể gánh đỡ cả khuôn mặt, giúp nửa đời sau vẫn an ổn dù tiền vận và trung vận chỉ bình thường.',
    ];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-10">
        <div class="relative flex shrink-0 gap-3 self-center sm:self-start">
            <div @class(['relative w-10 text-right font-serif font-semibold', 'tm-blurred' => $blurRatios])>
                @foreach ($zones as [$name, $ages, $middle, $ratio])
                    <span @class([
                        'absolute right-0 -translate-y-1/2',
                        'text-base text-copper-600' => $ratio === $largest,
                        'text-sm text-ink-400' => $ratio !== $largest,
                    ]) style="top: {{ $middle }}%">{{ $ratio ? "{$ratio}%" : '…' }}</span>
                @endforeach
            </div>

            <figure class="relative aspect-[250/255] w-48 overflow-hidden rounded-xl border border-cream-300 sm:w-56">
                <x-analysis.face-crop :analysis="$analysis" :box="$sections['overview']['content']['face_box'] ?? null" class="size-full" />
            </figure>

            <div class="relative w-24">
                @foreach ($zones as [$name, $ages, $middle])
                    <div class="absolute left-0 -translate-y-1/2 whitespace-nowrap" style="top: {{ $middle }}%">
                        <div class="text-sm font-semibold text-ink-600">{{ $name }}</div>
                        <div class="text-xs text-ink-400">{{ $ages }}</div>
                    </div>
                @endforeach
            </div>

            @foreach ($lines as $line)
                <div class="pointer-events-none absolute inset-x-0 border-t-2 border-dashed border-copper-500" style="top: {{ $line }}%"></div>
            @endforeach
        </div>

        <div class="min-w-0 flex-1">
            @if ($loading && $content['summary'] === [])
                <x-analysis.loading label="Đang đo tam đình…" />
            @else
                <x-analysis.summary :text="$content['summary']" :locked="$locked" />
            @endif

            @unless ($loading && $content['rating'] === 0)
                <div class="mt-4 flex items-center gap-3 text-sm font-semibold text-ink-600">
                    Đánh giá <x-ui.stars :rating="$content['rating']" :size="18" :class="$locked ? 'tm-blurred' : ''" />
                </div>
            @endunless
        </div>
    </div>

    <x-slot:detail>
        @if ($loading && collect($content['tabs'])->every(fn (array $tab): bool => $tab['blocks'] === []))
            <x-analysis.loading label="Đang viết luận giải chi tiết…" />
        @else
            <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" :intros="$zoneIntros" id="tam-dinh" />
        @endif
    </x-slot:detail>
</x-analysis.section>
