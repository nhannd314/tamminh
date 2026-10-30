<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Render the landing page with its static content blocks.
     */
    public function __invoke(): View
    {
        return view('pages.home', [
            'nguHanh' => $this->nguHanh(),
            'thanKhiSac' => $this->thanKhiSac(),
            'tamDinh' => $this->tamDinh(),
            'nguQuan' => $this->nguQuan(),
            'thapNhiCung' => $this->thapNhiCung(),
            'phongThuy' => $this->phongThuy(),
            'advice' => $this->advice(),
            'experts' => $this->experts(),
            'stats' => $this->stats(),
            'reviews' => $this->reviews(),
            'posts' => $this->posts(),
            'faqs' => $this->faqs(),
            'trustPoints' => $this->trustPoints(),
        ]);
    }

    /**
     * @return list<array{glyph: string, name: string, description: string}>
     */
    private function nguHanh(): array
    {
        return [
            ['glyph' => '金', 'name' => 'Kim', 'description' => 'Mặt vuông, da sáng - chính trực, quyết đoán, giỏi làm chủ.'],
            ['glyph' => '木', 'name' => 'Mộc', 'description' => 'Mặt dài thanh tú - nhân hậu, sáng tạo, luôn vươn lên.'],
            ['glyph' => '水', 'name' => 'Thủy', 'description' => 'Mặt tròn đầy đặn - linh hoạt, thông tuệ, ứng biến khéo.'],
            ['glyph' => '火', 'name' => 'Hỏa', 'description' => 'Mặt nhọn, nét sắc sảo - nhiệt huyết, có tài dẫn dắt.'],
            ['glyph' => '土', 'name' => 'Thổ', 'description' => 'Mặt dày, thần thái vững chãi - bao dung, điềm đạm, đáng tin.'],
        ];
    }

    /**
     * @return list<array{glyph: string, name: string, description: string}>
     */
    private function thanKhiSac(): array
    {
        return [
            ['glyph' => '神', 'name' => 'Thần', 'description' => 'Ánh mắt và tinh thần - phản chiếu nội lực, ý chí và chiều sâu tâm hồn. Mắt có thần là người minh mẫn, bền chí, dễ thành đạt.'],
            ['glyph' => '氣', 'name' => 'Khí', 'description' => 'Hơi thở và nội khí - biểu hiện sức khỏe, sinh lực và vận trình thịnh suy hiện tại. Khí đầy, điềm đạm là dấu hiệu vận đang lên.'],
            ['glyph' => '色', 'name' => 'Sắc', 'description' => 'Nước da và sắc diện - hé lộ cát hung, tài lộc và biến động sắp tới. Sắc hồng nhuận báo tin vui, sắc xỉn tối cần giữ gìn.'],
        ];
    }

    /**
     * @return list<array{glyph: string, name: string, range: string, meaning: string}>
     */
    private function tamDinh(): array
    {
        return [
            ['glyph' => '上', 'name' => 'Thượng đình', 'range' => 'Trán → chân mày', 'meaning' => 'Tổ tiên, trí tuệ, vận đầu đời (15–30)'],
            ['glyph' => '中', 'name' => 'Trung đình', 'range' => 'Chân mày → đầu mũi', 'meaning' => 'Bản thân, sự nghiệp, vận trung niên (31–50)'],
            ['glyph' => '下', 'name' => 'Hạ đình', 'range' => 'Đầu mũi → cằm', 'meaning' => 'Hậu vận, con cháu, phúc lộc cuối đời (51+)'],
        ];
    }

    /**
     * @return list<array{glyph: string, name: string, subtitle: string, description: string}>
     */
    private function nguQuan(): array
    {
        return [
            ['glyph' => '目', 'name' => 'Mắt', 'subtitle' => 'Giám sát quan', 'description' => 'Tâm tính, trí tuệ, vận 35–40'],
            ['glyph' => '眉', 'name' => 'Lông mày', 'subtitle' => 'Bảo thọ quan', 'description' => 'Tình cảm, anh em, vận 31–34'],
            ['glyph' => '耳', 'name' => 'Tai', 'subtitle' => 'Thái thính quan', 'description' => 'Phúc lộc, thọ mệnh, vận 1–14'],
            ['glyph' => '鼻', 'name' => 'Mũi', 'subtitle' => 'Thẩm biện quan', 'description' => 'Tài lộc, vợ chồng, vận 41–50'],
            ['glyph' => '口', 'name' => 'Miệng', 'subtitle' => 'Xuất nạp quan', 'description' => 'Phúc khẩu, con cái, vận 51+'],
        ];
    }

    /**
     * @return list<array{glyph: string, name: string, description: string}>
     */
    private function thapNhiCung(): array
    {
        return [
            ['glyph' => '命', 'name' => 'Mệnh cung', 'description' => 'Bản mệnh, tổng quan cuộc đời'],
            ['glyph' => '官', 'name' => 'Quan lộc cung', 'description' => 'Sự nghiệp, chức vụ, công danh'],
            ['glyph' => '財', 'name' => 'Tài bạch cung', 'description' => 'Tài lộc, tiền bạc, của cải'],
            ['glyph' => '遷', 'name' => 'Thiên di cung', 'description' => 'Di chuyển, xuất ngoại, đổi vận'],
            ['glyph' => '福', 'name' => 'Phúc đức cung', 'description' => 'Phúc khí, hưởng thụ, đức độ'],
            ['glyph' => '妻', 'name' => 'Phu thê cung', 'description' => 'Hôn nhân, vợ chồng, duyên phận'],
            ['glyph' => '子', 'name' => 'Tử tức cung', 'description' => 'Con cái, hậu duệ, sinh nở'],
            ['glyph' => '兄', 'name' => 'Huynh đệ cung', 'description' => 'Anh chị em, bạn bè thân cận'],
            ['glyph' => '僕', 'name' => 'Nô bộc cung', 'description' => 'Cấp dưới, người làm, đối tác'],
            ['glyph' => '父', 'name' => 'Phụ mẫu cung', 'description' => 'Cha mẹ, tổ tiên, gia đạo'],
            ['glyph' => '田', 'name' => 'Điền trạch cung', 'description' => 'Nhà cửa, đất đai, bất động sản'],
            ['glyph' => '疾', 'name' => 'Tật ách cung', 'description' => 'Sức khỏe, bệnh tật, tai ương'],
        ];
    }

    /**
     * @return list<array{icon: string, title: string, description: string}>
     */
    private function phongThuy(): array
    {
        return [
            ['icon' => 'sparkle', 'title' => 'Màu sắc phù hợp', 'description' => 'Màu tương sinh, tương hợp với bản mệnh để hút tài lộc, may mắn'],
            ['icon' => 'mountain', 'title' => 'Hướng phong thủy', 'description' => 'Hướng nhà, hướng bàn làm việc, hướng ngủ tốt cho từng năm'],
            ['icon' => 'star', 'title' => 'Đồ vật phong thủy', 'description' => 'Vật phẩm cát tường, đá quý, linh vật hộ mệnh nên đeo bên người'],
            ['icon' => 'users', 'title' => 'Tuổi hợp làm ăn', 'description' => 'Tuổi tương sinh để hợp tác, kết giao, tránh tuổi xung khắc'],
            ['icon' => 'message', 'title' => 'Số & ngày tốt', 'description' => 'Con số may mắn, ngày giờ hoàng đạo để khởi sự, ký kết, xuất hành'],
            ['icon' => 'shield', 'title' => 'Hóa giải sát khí', 'description' => 'Cách bố trí, trấn yểm hóa giải hung tinh và điểm xấu trong nhà'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function advice(): array
    {
        return [
            'Tâm pháp' => 'Tu tâm dưỡng tính, thiền định, hành thiện - gốc rễ của mọi cải vận.',
            'Hành động' => 'Việc nên làm, việc nên tránh trong từng đại vận và lưu niên.',
            'Thời điểm' => 'Năm tốt, tháng tốt, ngày giờ hoàng đạo cho các quyết định lớn.',
            'Vật phẩm' => 'Linh vật, vật phẩm phong thủy, hướng nhà - hỗ trợ thực tế.',
        ];
    }

    /**
     * @return list<array{glyph: string, name: string, role: string, years: int, sessions: int, photo: ?string}>
     */
    private function experts(): array
    {
        $photo = file_exists(public_path('images/thay-tuong-so.png')) ? asset('images/thay-tuong-so.png') : null;

        return [
            ['glyph' => '明', 'name' => 'Thầy Minh Quang', 'role' => 'Chuyên gia tướng số', 'years' => 30, 'sessions' => 1287, 'photo' => $photo],
            ['glyph' => '太', 'name' => 'Thầy Tấn Phát', 'role' => 'Chuyên gia tướng số', 'years' => 25, 'sessions' => 956, 'photo' => $photo],
            ['glyph' => '禄', 'name' => 'Thầy Thiên Lộc', 'role' => 'Chuyên gia tướng số', 'years' => 20, 'sessions' => 743, 'photo' => $photo],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function stats(): array
    {
        return [
            ['value' => '8.500+', 'label' => 'Khuôn mặt đã luận giải'],
            ['value' => '4.9/5', 'label' => 'Điểm đánh giá trung bình'],
            ['value' => '97%', 'label' => 'Khách hàng giới thiệu bạn bè'],
            ['value' => '28', 'label' => 'Thầy tướng số & chuyên gia chỉ tay'],
        ];
    }

    /**
     * @return list<array{rating: int, quote: string, name: string, role: string, service: string, avatar: string, tone: string}>
     */
    private function reviews(): array
    {
        return [
            ['rating' => 5, 'quote' => 'Báo cáo của Tâm Minh chi tiết đến từng cung mệnh, đọc xong mình thấy hiểu chính mình hơn - kiểu tâm lý học chứ không phải bói toán mê tín. Câu chuyện rất riêng tư, không có cảm giác "văn mẫu".', 'name' => 'Nguyễn Thanh Linh', 'role' => 'Nhà thiết kế · Hà Nội', 'service' => 'Luận giải tử vi trọn đời', 'avatar' => 'L', 'tone' => 'copper'],
            ['rating' => 5, 'quote' => 'Công nghệ phân tích khuôn mặt nhận diện tướng mạo khá chính xác, nhưng điều mình ấn tượng là phần Thầy luận giải bằng văn phong gần gũi. Mình hỏi gì cũng được trả lời cặn kẽ, không phải kiểu chốt sale dịch vụ.', 'name' => 'Trần Minh Tuấn', 'role' => 'Giám đốc kinh doanh · TP.HCM', 'service' => 'Xem tướng số cùng Thầy Minh Quang', 'avatar' => 'T', 'tone' => 'navy'],
            ['rating' => 5, 'quote' => 'Cô từng đi xem ở nhiều nơi nhưng nội dung na ná nhau. Tâm Minh hỏi rất kỹ về hoàn cảnh, bài luận viết riêng cho mình. Phần phong thủy nhà ở thực sự hữu ích, đã áp dụng và thấy gia đạo êm ấm hơn.', 'name' => 'Lê Thị Hương', 'role' => 'Chủ doanh nghiệp · Đà Nẵng', 'service' => 'Phong thủy nhà ở', 'avatar' => 'H', 'tone' => 'copper'],
            ['rating' => 5, 'quote' => 'Mình hoài nghi mấy kiểu xem tướng online, nhưng Tâm Minh khác - họ giải thích "tại sao" chứ không phán xét. Phần lời khuyên cải vận rất thực tế: nên làm gì, tránh gì, không có vẽ vời.', 'name' => 'Phạm Đức Anh', 'role' => 'Lập trình viên · Hà Nội', 'service' => 'Luận giải nhân tướng', 'avatar' => 'Đ', 'tone' => 'navy'],
            ['rating' => 5, 'quote' => 'Thầy Huyền Lâm tư vấn cho mình về sự nghiệp và hôn nhân rất tâm huyết. Mỗi lần nhắn tin Thầy đều phản hồi nhanh, lời khuyên đúng trọng tâm. Đáng tiền và đáng trân quý.', 'name' => 'Vũ Thu Hoa', 'role' => 'Giáo viên · Cần Thơ', 'service' => 'Tư vấn 1-1 với Thầy Huyền Lâm', 'avatar' => 'H', 'tone' => 'copper'],
            ['rating' => 5, 'quote' => 'Thầy xem chỉ tay cho mình rất kỹ - đường sinh đạo, trí đạo, tâm đạo đều giải thích cặn kẽ, nhiều điều nói trúng những chuyện mình đã trải qua. Mức độ chuyên môn ở đây thực sự nghiêm túc.', 'name' => 'Hoàng Văn Khải', 'role' => 'Doanh nhân · TP.HCM', 'service' => 'Xem chỉ tay với chuyên gia', 'avatar' => 'K', 'tone' => 'navy'],
        ];
    }

    /**
     * @return list<array{category: string, glyph: string, tone: string, title: string, excerpt: string, author: string, date: string, minutes: int}>
     */
    private function posts(): array
    {
        return [
            ['category' => 'Tướng pháp', 'glyph' => '相', 'tone' => 'copper', 'title' => 'Đọc khuôn mặt qua thần - khí - sắc: ba tầng năng lượng người xưa tin cậy', 'excerpt' => 'Trước cả khi đếm ngũ quan, các Thầy tướng số cổ truyền đã dạy phải đọc "thần" trước tiên. Bài viết giải mã ba lớp năng lượng trên khuôn mặt mỗi người.', 'author' => 'Thầy Minh Quang', 'date' => '12 thg 5, 2026', 'minutes' => 8],
            ['category' => 'Tử vi', 'glyph' => '紫', 'tone' => 'navy', 'title' => 'Năm Bính Ngọ 2026: vận khí 12 con giáp và những điều cần lưu ý', 'excerpt' => 'Lưu niên 2026 đem theo sự dịch chuyển của Thái Tuế, Tam Tai và Kim Lâu. Phân tích chi tiết vận khí từng tuổi cùng phương pháp hóa giải.', 'author' => 'Thầy Huyền Lâm', 'date' => '08 thg 5, 2026', 'minutes' => 12],
            ['category' => 'Phong thủy', 'glyph' => '風', 'tone' => 'cream', 'title' => 'Bài trí phòng làm việc theo bản mệnh - cách hút tài lộc không cần linh vật', 'excerpt' => 'Ngũ hành bản mệnh quyết định hướng bàn, màu sơn, ánh sáng và cây xanh. Hướng dẫn tự kiểm tra phòng làm việc trong 10 phút.', 'author' => 'Thầy An Khang', 'date' => '02 thg 5, 2026', 'minutes' => 6],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function faqs(): array
    {
        return [
            ['question' => 'Tâm Minh là dịch vụ như thế nào?', 'answer' => 'Tâm Minh là nền tảng xem tướng số và chỉ tay, kết hợp công nghệ phân tích khuôn mặt với đội ngũ chuyên gia nhân tướng hàng đầu Việt Nam. Bạn có thể nhận báo cáo luận giải chi tiết hoặc đặt tư vấn 1-1 với các Thầy có 18-30 năm kinh nghiệm.'],
            ['question' => 'Công nghệ phân tích khuôn mặt chính xác đến đâu?', 'answer' => 'Hệ thống đo đạc và tái tạo chính xác hàng trăm điểm mốc trên khuôn mặt - tỷ lệ tam đình, hình dáng ngũ quan, đường nét và khí sắc - rồi đối chiếu với tướng pháp truyền thống. Phần luận giải sâu về vận hạn, tài lộc, tình duyên do Thầy trực tiếp phụ trách.'],
            ['question' => 'Cần chuẩn bị ảnh như thế nào?', 'answer' => 'Chụp chính diện, đủ sáng, không đeo kính, không dùng filter làm mịn da. Ảnh rõ nét giúp công nghệ tái tạo khuôn mặt chính xác hơn, từ đó kết quả luận giải sát với tướng mạo thật của bạn.'],
            ['question' => 'Thông tin cá nhân và ảnh của tôi được bảo mật ra sao?', 'answer' => 'Ảnh chân dung được mã hóa và chỉ dùng cho việc phân tích duy nhất của bạn - không lưu trữ lâu dài, không chia sẻ cho bên thứ ba. Bạn có thể yêu cầu xóa toàn bộ dữ liệu bất cứ lúc nào.'],
            ['question' => 'Báo cáo được gửi trong bao lâu?', 'answer' => 'Báo cáo phân tích khuôn mặt: vài phút. Báo cáo có Thầy luận giải: 24-48 giờ. Tư vấn 1-1 trực tiếp: đặt lịch trong vòng 24 giờ. Mức độ chuyên sâu càng cao thì thời gian luận giải càng kỹ.'],
            ['question' => 'Chi phí mỗi loại dịch vụ là bao nhiêu?', 'answer' => 'Xem tướng cơ bản: miễn phí. Luận giải nhân tướng chuyên sâu: 199.000đ. Luận giải có Thầy phản biện: từ 599.000đ. Xem chỉ tay / tư vấn 1-1 với Thầy: từ 1.200.000đ/giờ tùy chuyên gia. Tất cả minh bạch trước khi thanh toán.'],
            ['question' => 'Tôi không hài lòng với kết quả thì sao?', 'answer' => 'Tâm Minh cam kết hoàn 100% phí dịch vụ trong vòng 7 ngày nếu bạn không hài lòng với báo cáo, không cần lý do. Riêng các buổi tư vấn 1-1 có thể được xếp lại với Thầy khác trong cùng cấp độ.'],
            ['question' => 'Xem tướng có phải mê tín không?', 'answer' => 'Tâm Minh không xem tướng số như "định mệnh không thể đổi". Chúng tôi coi đó là công cụ tự nhận thức - giúp bạn hiểu xu hướng tính cách, điểm mạnh, điểm yếu - để chủ động cải vận, chứ không phán xét.'],
        ];
    }

    /**
     * @return list<array{icon: string, title: string, description: string}>
     */
    private function trustPoints(): array
    {
        return [
            ['icon' => 'shield', 'title' => 'Bảo mật thông tin', 'description' => 'Cam kết bảo mật tuyệt đối thông tin cá nhân của bạn'],
            ['icon' => 'yinyang', 'title' => 'Luận giải chính xác', 'description' => 'Công nghệ phân tích khuôn mặt cùng chuyên gia hàng đầu'],
            ['icon' => 'users', 'title' => 'Chuyên gia uy tín', 'description' => 'Đội ngũ chuyên gia nhiều năm kinh nghiệm, tận tâm'],
            ['icon' => 'headset', 'title' => 'Hỗ trợ tận tâm', 'description' => 'Hỗ trợ nhanh chóng 24/7 mọi lúc mọi nơi'],
        ];
    }
}
