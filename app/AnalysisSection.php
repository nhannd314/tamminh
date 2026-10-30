<?php

namespace App;

use App\Models\Setting;

/**
 * Các phần của bản luận giải, theo thứ tự hiển thị.
 * Tổng quan luôn miễn phí; phần 1-7 có giá lẻ trong settings (`pricing.section.{value}`).
 */
enum AnalysisSection: string
{
    case Overview = 'overview';
    case NguHanh = 'ngu_hanh';
    case ThanKhiSac = 'than_khi_sac';
    case TamDinh = 'tam_dinh';
    case NguQuan = 'ngu_quan';
    case ThapNhiCung = 'thap_nhi_cung';
    case PhongThuy = 'phong_thuy';
    case Advice = 'advice';

    public function label(): string
    {
        return match ($this) {
            self::Overview => 'Tổng quan',
            self::NguHanh => 'Ngũ hành tướng pháp',
            self::ThanKhiSac => 'Tổng quan thần khí sắc',
            self::TamDinh => 'Tam đình',
            self::NguQuan => 'Ngũ quan',
            self::ThapNhiCung => 'Thập nhị cung mệnh',
            self::PhongThuy => 'Phong thủy ứng dụng',
            self::Advice => 'Lời khuyên cải vận',
        };
    }

    /**
     * Id của phần trên trang kết quả, dùng cho mục lục.
     */
    public function anchor(): string
    {
        return str_replace('_', '-', $this->value);
    }

    /**
     * Mô tả ngắn hiển thị khi phần này còn khoá.
     */
    public function teaser(): string
    {
        return match ($this) {
            self::Overview => 'Tổng quan vận mệnh theo bát tự và nạp âm bản mệnh.',
            self::NguHanh => 'Khuôn mặt thuộc hành nào, ngũ hành vượng - suy và cách cân bằng.',
            self::ThanKhiSac => 'Thần, khí, sắc trên gương mặt - phản chiếu nội lực, sức khỏe và vận trình gần.',
            self::TamDinh => 'Thượng - trung - hạ đình: vận đầu đời, trung niên và hậu vận của bạn.',
            self::NguQuan => 'Mày, mắt, tai, mũi, miệng - năm giác quan nói gì về tính cách và vận mệnh.',
            self::ThapNhiCung => 'Mười hai cung trên gương mặt: sự nghiệp, tài lộc, hôn nhân, sức khỏe…',
            self::PhongThuy => 'Màu sắc, hướng nhà, vật phẩm và con số hợp bản mệnh.',
            self::Advice => 'Lời khuyên cải vận dành riêng cho bạn: nên làm, nên tránh theo từng giai đoạn.',
        };
    }

    /**
     * Số thứ tự "Phần N"; Tổng quan không đánh số.
     */
    public function number(): ?int
    {
        $index = array_search($this, self::cases(), true);

        return $index === 0 ? null : $index;
    }

    public function isFree(): bool
    {
        return $this === self::Overview
            || in_array($this->value, Setting::get('analysis.free_sections', []), true);
    }

    public function price(): int
    {
        return (int) Setting::get("pricing.section.{$this->value}", 0);
    }

    /**
     * @return list<self>
     */
    public static function free(): array
    {
        return array_values(array_filter(self::cases(), fn (self $section): bool => $section->isFree()));
    }

    /**
     * @return list<self>
     */
    public static function paid(): array
    {
        return array_values(array_filter(self::cases(), fn (self $section): bool => ! $section->isFree()));
    }
}
