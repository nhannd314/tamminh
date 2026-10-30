<?php

namespace App\Support\Astrology;

/**
 * Một cặp Thiên can - Địa chi (một "trụ" trong bát tự).
 */
final readonly class Pillar
{
    public const STEMS = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];

    public const BRANCHES = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];

    /**
     * Ngũ hành của 10 can, theo thứ tự STEMS.
     */
    private const STEM_ELEMENTS = [
        Element::Moc, Element::Moc, Element::Hoa, Element::Hoa, Element::Tho,
        Element::Tho, Element::Kim, Element::Kim, Element::Thuy, Element::Thuy,
    ];

    /**
     * Ngũ hành của 12 chi, theo thứ tự BRANCHES.
     */
    private const BRANCH_ELEMENTS = [
        Element::Thuy, Element::Tho, Element::Moc, Element::Moc, Element::Tho, Element::Hoa,
        Element::Hoa, Element::Tho, Element::Kim, Element::Kim, Element::Tho, Element::Thuy,
    ];

    /**
     * 30 nạp âm của lục thập hoa giáp, mỗi nạp âm ứng với 2 năm liên tiếp.
     *
     * @var list<array{0: string, 1: Element}>
     */
    private const NAP_AM = [
        ['Hải Trung Kim', Element::Kim], ['Lư Trung Hỏa', Element::Hoa], ['Đại Lâm Mộc', Element::Moc],
        ['Lộ Bàng Thổ', Element::Tho], ['Kiếm Phong Kim', Element::Kim], ['Sơn Đầu Hỏa', Element::Hoa],
        ['Giản Hạ Thủy', Element::Thuy], ['Thành Đầu Thổ', Element::Tho], ['Bạch Lạp Kim', Element::Kim],
        ['Dương Liễu Mộc', Element::Moc], ['Tuyền Trung Thủy', Element::Thuy], ['Ốc Thượng Thổ', Element::Tho],
        ['Tích Lịch Hỏa', Element::Hoa], ['Tùng Bách Mộc', Element::Moc], ['Trường Lưu Thủy', Element::Thuy],
        ['Sa Trung Kim', Element::Kim], ['Sơn Hạ Hỏa', Element::Hoa], ['Bình Địa Mộc', Element::Moc],
        ['Bích Thượng Thổ', Element::Tho], ['Kim Bạch Kim', Element::Kim], ['Phú Đăng Hỏa', Element::Hoa],
        ['Thiên Hà Thủy', Element::Thuy], ['Đại Trạch Thổ', Element::Tho], ['Thoa Xuyến Kim', Element::Kim],
        ['Tang Đố Mộc', Element::Moc], ['Đại Khê Thủy', Element::Thuy], ['Sa Trung Thổ', Element::Tho],
        ['Thiên Thượng Hỏa', Element::Hoa], ['Thạch Lựu Mộc', Element::Moc], ['Đại Hải Thủy', Element::Thuy],
    ];

    public function __construct(
        public int $stem,
        public int $branch,
    ) {}

    public static function ofYear(int $year): self
    {
        return new self(self::mod($year - 4, 10), self::mod($year - 4, 12));
    }

    public static function mod(int $value, int $modulus): int
    {
        return (($value % $modulus) + $modulus) % $modulus;
    }

    public function name(): string
    {
        return self::STEMS[$this->stem].' '.self::BRANCHES[$this->branch];
    }

    public function stemName(): string
    {
        return self::STEMS[$this->stem];
    }

    public function branchName(): string
    {
        return self::BRANCHES[$this->branch];
    }

    public function stemElement(): Element
    {
        return self::STEM_ELEMENTS[$this->stem];
    }

    public function branchElement(): Element
    {
        return self::BRANCH_ELEMENTS[$this->branch];
    }

    /**
     * Vị trí 0-59 trong lục thập hoa giáp (Giáp Tý = 0).
     */
    public function cycleIndex(): int
    {
        return self::mod(6 * $this->stem - 5 * $this->branch, 60);
    }

    public function napAm(): string
    {
        return self::NAP_AM[intdiv($this->cycleIndex(), 2)][0];
    }

    public function napAmElement(): Element
    {
        return self::NAP_AM[intdiv($this->cycleIndex(), 2)][1];
    }
}
