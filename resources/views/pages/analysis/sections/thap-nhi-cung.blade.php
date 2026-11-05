@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: chỉ hiện các cung đã có chữ, chưa có cung nào thì hiện spinner.
    $loading = $item['loading'];
    $palaces = $loading ? array_filter($content['palaces'], fn (array $palace): bool => filled($palace['text'])) : $content['palaces'];
    $verdictClasses = [
        'Cát' => 'bg-success/10 text-success',
        'Trung bình' => 'bg-copper-50 text-copper-700',
        'Cần lưu ý' => 'bg-danger/10 text-danger',
    ];

    // Mô tả cố định cho từng cung: [ý nghĩa, tướng cát, tướng hung].
    $palaceGuides = [
        'Mệnh cung' => ['Là "hạt nhân" quản lý vận mệnh tổng quan: ý chí, năng lực tư duy, vận khí hiện tại và khả năng vượt qua nghịch cảnh.', 'Đầy đặn, phẳng nhẵn, sáng sủa, rộng khoảng 1,5–2 ngón tay: tư duy cởi mở, vận khí hanh thông, dễ thành công.', 'Có nốt ruồi đen, sẹo, lõm hoặc lông mày giao nhau chèn ép: hay lo âu, vận trình nhiều trắc trở.'],
        'Quan lộc cung' => ['Thể hiện con đường công danh, sự nghiệp, vị thế xã hội và quan hệ với cấp trên hay chính quyền.', 'Trán nhô tròn, cao rộng, xương lông mày không quá gồ: quan lộ thuận lợi, dễ nắm giữ vị trí lãnh đạo.', 'Trán vát, lõm, có nốt ruồi xấu hoặc sẹo ngang: sự nghiệp dễ gặp sóng gió, vất vả nửa đầu đời.'],
        'Tài bạch cung' => ['Quản lý khả năng tạo ra tiền bạc, tích lũy tài sản và tư duy quản lý tài chính.', 'Sống mũi thẳng, chóp mũi tròn trịa, cánh mũi dày dặn: tiền tài dồi dào, giữ được của.', 'Sống mũi gãy hoặc lệch, lộ xương, lỗ mũi quá hếch: tiền vào tay này ra tay khác, kinh doanh dễ rủi ro.'],
        'Điền trạch cung' => ['Phản ánh việc sở hữu đất đai, nhà cửa, tài sản cố định và sự an yên trong gia đình.', 'Vùng da đầy đặn, thanh tú, không sẹo: dễ thừa hưởng hoặc tự mua được bất động sản, gia đạo ấm êm.', 'Trũng hóp, quá hẹp, có nốt ruồi đen hoặc mạch máu đỏ chăng lưới: khó tích lũy đất đai, dễ tranh chấp tài sản.'],
        'Huynh đệ cung' => ['Tượng trưng cho tình cảm anh em ruột thịt, bạn bè, đồng nghiệp và mạng lưới xã giao.', 'Lông mày mọc xuôi, thanh tú, dài quá mắt: anh em hòa thuận, ra ngoài được bạn bè tương trợ.', 'Lông mày thô, mọc ngược, đứt đoạn hoặc quá thưa: tình anh em phai nhạt, dễ bị bạn bè chơi xấu.'],
        'Phu thê cung' => ['Thể hiện nhân duyên, tình cảm vợ chồng, độ bền vững của hôn nhân và sức khỏe của người phối ngẫu.', 'Đầy đặn, khí sắc hồng hào, không có nếp nhăn loạn: hôn nhân hạnh phúc, vợ chồng đồng lòng.', 'Hóp sâu, có sẹo, nốt ruồi đen hoặc chân chim chăng lưới sớm: hôn nhân dễ bất hòa, gập ghềnh.'],
        'Tử tức cung' => ['Thể hiện đường con cái (khả năng sinh sản, vận mệnh của con) và sức khỏe hệ sinh dục.', 'Bọng mắt hơi nảy nở, khí sắc tươi sáng, mịn màng: con ngoan hiếu thảo, bản thân sức khỏe tốt.', 'Khô hóp, thâm đen như quầng thâm, có sẹo hoặc nốt ruồi: đường con cái lận đận, vất vả lo toan cho con.'],
        'Nô bộc cung' => ['Quản lý mối quan hệ với cấp dưới, nhân viên, người giúp việc, người ủng hộ và khả năng lãnh đạo tập thể.', 'Cằm nảy nở, vuông vắn hoặc tròn trịa, xương hàm vững: có uy quyền, cấp dưới trung thành, quy tụ được người giúp sức.', 'Cằm lẹm, nhọn hoắt, hóp vào: làm chủ dễ bị phản bội, khó quản lý nhân sự.'],
        'Tật ách cung' => ['Phản ánh sức đề kháng, thể chất bẩm sinh, khả năng chống chọi bệnh tật và các tai hạn bất ngờ.', 'Gốc mũi cao nhô, liền mạch với trán, sống mũi thẳng: sức khỏe dồi dào, ít bệnh vặt, gặp nguy hóa an.', 'Sơn căn quá thấp lõm, có nếp nhăn ngang hoặc nốt ruồi đen: thể chất yếu, trung niên dễ gặp biến cố sức khỏe.'],
        'Thiên di cung' => ['Thể hiện việc di chuyển, đi lại, xuất ngoại, định cư hoặc khả năng thích nghi khi làm việc xa nhà.', 'Góc trán nảy nở, sáng sủa, không sẹo: đi xa gặp may mắn, dễ thành công khi lập nghiệp phương xa.', 'Khuyết lõm, xám xịt hoặc có sẹo: xuất hành dễ trắc trở, làm ăn xa nhà hay bị gián đoạn.'],
        'Phụ mẫu cung' => ['Thể hiện sức khỏe, tuổi thọ của cha mẹ cùng sự giáo dưỡng và phúc đức từ gia đình (nhật giác bên trái ứng với cha, nguyệt giác bên phải ứng với mẹ).', 'Nhô nhẹ cân đối, da sáng mịn: cha mẹ sống thọ, gia đạo êm ấm, được định hướng tốt từ nhỏ.', 'Một bên lệch lõm, có nốt ruồi hoặc sẹo chèn ép: tình cảm với cha mẹ xung khắc hoặc sớm phải sống xa gia đình.'],
        'Phúc đức cung' => ['Thể hiện phúc đức tổ tiên để lại, tinh thần an lạc và khả năng tận hưởng cuộc sống của bản thân.', 'Đầy đặn, khí sắc nhuận: tâm tính bình an, gặp dữ hóa lành, đời sống tinh thần phong phú.', 'Khô hẹp, trũng sâu: hay lo lắng vặt, nhiều trắc trở, phải tự lực gánh vác, ít được trợ lực từ gia tộc.'],
    ];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    @if ($loading && blank($content['summary']))
        <x-analysis.loading label="Đang xem thập nhị cung…" />
    @else
        <x-analysis.summary :text="$content['summary']" :locked="$locked" />
    @endif

    <x-slot:detail>
        @if ($loading && $palaces === [])
            <x-analysis.loading label="Đang luận giải từng cung…" />
        @endif

        <div class="grid grid-cols-1 items-start gap-3 sm:grid-cols-2">
            @foreach ($palaces as $palace)
                <details class="group flex min-w-0 flex-col rounded-lg border border-cream-300 bg-cream-50 p-4 open:col-span-full open:bg-white open:shadow-soft-sm">
                    <summary class="flex cursor-pointer list-none flex-col gap-1.5 [&::-webkit-details-marker]:hidden">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-baseline gap-2">
                                <span class="font-serif text-sm font-semibold text-copper-500">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h4 class="font-serif text-lg font-semibold text-ink-600">{{ $palace['name'] }}</h4>
                            </div>
                            @if (filled($palace['verdict']))
                                <span @class(['shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold', $verdictClasses[$palace['verdict']] ?? $verdictClasses['Trung bình'], 'tm-blurred' => $locked])>{{ $palace['verdict'] }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-ink-300">Vị trí: {{ $palace['position'] }}</div>
                        <p @class(['text-[17px] text-ink-500', 'tm-blurred' => $locked])>{{ $palace['text'] }}</p>
                        <x-ui.icon name="chevron-down" :size="16" class="mt-1 text-copper-600 transition-transform group-open:rotate-180" />
                    </summary>

                    <div class="mt-3 flex flex-col gap-2 border-t border-cream-300 pt-3 text-[17px] text-ink-500">
                        @isset($palaceGuides[$palace['name']])
                            @php([$guideMeaning, $guideGood, $guideBad] = $palaceGuides[$palace['name']])
                            <ul class="flex list-disc flex-col gap-1.5 pl-5 marker:text-copper-500">
                                <li><b class="font-semibold text-copper-700">Ý nghĩa:</b> {{ $guideMeaning }}</li>
                                <li><b class="font-semibold text-success">Tướng cát:</b> {{ $guideGood }}</li>
                                <li><b class="font-semibold text-danger">Tướng hung:</b> {{ $guideBad }}</li>
                            </ul>
                        @endisset
                        <p><b class="font-semibold text-copper-700">Luận giải:</b> <span @class(['tm-blurred' => $locked])>{{ $palace['analysis'] }}</span></p>
                        <p><b class="font-semibold text-copper-700">Lời khuyên:</b> <span @class(['tm-blurred' => $locked])>{{ $palace['advice'] }}</span></p>
                    </div>
                </details>
            @endforeach
        </div>
    </x-slot:detail>
</x-analysis.section>
