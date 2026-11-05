@php
    $content = $item['content'];
    $locked = ! $item['unlocked'];
    // Đang chờ Gemini: mục nào chưa có chữ thì hiện spinner.
    $loading = $item['loading'];

    $featureIntros = [
        'Lông mày (Bảo thọ quan, ứng với Mộc tinh) tượng trưng cho anh em, bạn bè, ý chí, khí phách và danh tiếng, đồng thời thể hiện độ sâu sắc trong suy nghĩ và cách cư xử xã hội. Mày thanh tú, sợi dài nhỏ, vượt khỏi đuôi mắt, đen mượt là dấu hiệu người có nghĩa khí, được anh em nể trọng và dễ gặp quý nhân. Mày thô, mọc ngược, đứt đoạn hoặc liền sát ấn đường thường báo hiệu tính nóng nảy, hay tính toán tiểu tiết, quan hệ bạn bè anh em dễ bất hòa.',
        'Mắt (Giám sát quan, ứng với Hỏa tinh) được xem là quan trọng nhất trong ngũ quan, đại diện cho tâm linh, trí tuệ, ý chí lực và sự minh bạch — người xưa nói "tâm ở trong lòng, tướng hiện ra ở mắt". Mắt có thần quang, tròng đen trắng phân minh, đuôi mắt hướng lên hoặc phẳng thể hiện sự minh mẫn, quyết đoán, tầm nhìn xa và tư duy nhạy bén. Mắt lờ đờ, vô thần, lộ tròng trắng hoặc ánh nhìn láo liên thường báo hiệu tâm lý bất ổn, dễ đa nghi và nhiều trắc trở nội tâm.',
        'Tai (Thái thính quan, ứng với Kim tinh) đại diện cho phúc đức bẩm sinh, nền tảng gia thế, sức khỏe thể chất và tuổi thọ — những gì được thừa hưởng từ tổ tiên. Tai to, dày, vành tai rõ, dái tai nảy nở và vị trí cao ngang hoặc qua khỏi lông mày là dấu hiệu thông minh, sức khỏe dồi dào, thọ cao và có phúc khí lớn. Tai nhỏ, mỏng, dái tai nhọn hoặc bám sát đầu thường báo hiệu sức khỏe bẩm sinh yếu, thời trẻ vất vả, khó nhận được sự hỗ trợ từ gia tộc.',
        'Mũi (Thẩm biện quan, ứng với Thổ tinh) nằm ở trung tâm khuôn mặt, quản lý tài lộc (Tài bạch cung), khả năng tự chủ và lòng tự trọng; nó phản ánh cách một người kiếm tiền, giữ tiền và giá trị bản thân. Sống mũi thẳng, cao vừa phải, chóp mũi đầy đặn, cánh mũi nảy nở nhưng không hếch là tướng có năng lực quản lý tài chính tốt, trung thực và sự nghiệp vững vàng ở trung vận. Sống mũi gồ ghề, gãy khúc, lệch hoặc cánh mũi mỏng, hếch lộ lỗ mũi thường báo hiệu tài vận bất ổn, kiếm bao nhiêu tiêu hết bấy nhiêu, khó tích lũy.',
        'Miệng (Xuất nạp quan, ứng với Thủy tinh) tượng trưng cho khả năng ăn nói, lộc ăn uống, phúc lộc tuổi già và quan hệ con cái (Tử tức cung); đây là cửa ngõ thu nạp thức ăn và phát ra ngôn từ. Miệng rộng, môi đầy hồng hào, khóe miệng hướng lên, răng đều trắng là dấu hiệu giao tiếp khéo léo, hậu vận an nhàn và con cháu hiếu thuận. Môi quá mỏng, khóe miệng trĩu xuống, miệng quá nhỏ hoặc răng lộn xộn thường báo hiệu hay thị phi, lời nói thiếu sức nặng, đường con cái hoặc hậu vận cô đơn.',
    ];
@endphp

<x-analysis.section :analysis="$analysis" :item="$item">
    @if ($loading && $content['summary'] === [])
        <x-analysis.loading label="Đang xem ngũ quan…" />
    @else
        <x-analysis.summary :text="$content['summary']" :locked="$locked" />
    @endif

    <div class="mt-4 flex max-w-[860px] flex-col gap-3 text-[17px] text-ink-500">
        <p><b class="font-semibold text-copper-700">Nguyên lý phối hợp ngũ quan:</b> một quan dù đẹp đến đâu mà xung khắc với các quan khác thì cũng giảm đi cát khí. Mắt sáng cần lông mày thanh che chở; mũi tài lộc cần miệng và tai làm hậu thuẫn cân đối.</p>
        <p>Lý tưởng nhất là "ngũ quan triều cống": các quan đều hướng vào trung tâm — mắt nhìn thẳng tinh anh, mũi ngay ngắn, miệng khép chặt, tai áp sát đầu, lông mày ôm lấy mắt. Đó là dấu hiệu nội tâm quy tụ, tinh thần vững vàng, tâm trí không phân tán nên làm việc gì cũng dễ thành đại sự.</p>
    </div>

    <x-slot:detail>
        @if ($loading && collect($content['tabs'])->every(fn (array $tab): bool => $tab['blocks'] === []))
            <x-analysis.loading label="Đang viết luận giải chi tiết…" />
        @else
            <x-analysis.tabs :tabs="$content['tabs']" :locked="$locked" :intros="$featureIntros" id="ngu-quan" />
        @endif
    </x-slot:detail>
</x-analysis.section>
