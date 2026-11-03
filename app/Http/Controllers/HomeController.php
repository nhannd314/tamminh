<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\FaceAnalysis;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class HomeController extends Controller
{
    /**
     * Render the landing page with its static content blocks and the latest posts.
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
     * 3 chuyên gia đang hiển thị, theo thứ tự sắp xếp.
     *
     * @return Collection<int, Expert>
     */
    private function experts(): Collection
    {
        return Expert::query()->active()->orderBy('sort')->orderBy('id')->limit(3)->get();
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
     * Cảm nhận khách hàng quản lý ở trang Cấu hình admin, chưa lưu thì dùng mặc định trong config/site.php.
     *
     * @return list<array{rating: int, quote: string, name: string, role: string, service: string, avatar: string, tone: string}>
     */
    private function reviews(): array
    {
        return Setting::get('home.testimonials', config('site.testimonials', []));
    }

    /**
     * 3 bài viết mới nhất đã đăng.
     *
     * @return Collection<int, Post>
     */
    private function posts(): Collection
    {
        return Post::query()->published()->with('category')->latest('published_at')->limit(3)->get();
    }

    /**
     * Câu hỏi thường gặp quản lý ở trang Cấu hình admin (mặc định trong config/site.php); điền số ngày lưu ảnh thật vào `:portrait_days`.
     *
     * @return list<array{question: string, answer: string}>
     */
    private function faqs(): array
    {
        return array_map(fn (array $faq): array => [
            'question' => $faq['question'],
            'answer' => str_replace(':portrait_days', (string) FaceAnalysis::PORTRAIT_RETENTION_DAYS, $faq['answer']),
        ], Setting::get('home.faqs', config('site.faqs', [])));
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
