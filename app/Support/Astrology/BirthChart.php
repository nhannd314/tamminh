<?php

namespace App\Support\Astrology;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Lá số cơ bản từ ngày giờ sinh dương lịch (giờ Việt Nam).
 *
 * Quy ước:
 * - Trụ năm, trụ tháng của bát tự đổi theo tiết khí (năm đổi ở Lập Xuân).
 * - Sinh từ 23:00 tính là giờ Tý của ngày hôm sau (trụ ngày, trụ giờ).
 * - Tử vi (Cục, Thân cư) dùng năm/tháng âm lịch; tháng nhuận tính như tháng chính.
 * - Không có giờ sinh thì không có trụ giờ, Cục mệnh và Thân cư.
 */
final class BirthChart
{
    public const PALACES = [
        'Mệnh', 'Phụ Mẫu', 'Phúc Đức', 'Điền Trạch', 'Quan Lộc', 'Nô Bộc',
        'Thiên Di', 'Tật Ách', 'Tài Bạch', 'Tử Tức', 'Phu Thê', 'Huynh Đệ',
    ];

    public readonly LunarDate $lunarDate;

    public readonly Pillar $yearPillar;

    public readonly Pillar $monthPillar;

    public readonly Pillar $dayPillar;

    public readonly ?Pillar $hourPillar;

    public function __construct(
        public readonly CarbonImmutable $birthDate,
        public readonly ?int $hour = null,
        public readonly ?int $minute = null,
    ) {
        $this->lunarDate = LunarCalendar::toLunar($birthDate->day, $birthDate->month, $birthDate->year);

        $julianDay = LunarCalendar::julianDay($birthDate->day, $birthDate->month, $birthDate->year);
        $hours = $hour === null ? 12.0 : $hour + ($minute ?? 0) / 60;
        $sunLongitude = LunarCalendar::sunLongitude($julianDay - 0.5 + ($hours - LunarCalendar::TIME_ZONE) / 24);

        $solarYear = $birthDate->month <= 2 && $sunLongitude >= 270 && $sunLongitude < 315
            ? $birthDate->year - 1
            : $birthDate->year;
        $this->yearPillar = Pillar::ofYear($solarYear);

        $monthOffset = (int) floor(Pillar::mod((int) floor($sunLongitude) - 315, 360) / 30);
        $this->monthPillar = new Pillar(
            Pillar::mod(self::tigerMonthStem($this->yearPillar->stem) + $monthOffset, 10),
            Pillar::mod(2 + $monthOffset, 12),
        );

        $dayNumber = $julianDay + ($hour === 23 ? 1 : 0);
        $this->dayPillar = new Pillar(Pillar::mod($dayNumber + 9, 10), Pillar::mod($dayNumber + 1, 12));

        $this->hourPillar = $hour === null ? null : new Pillar(
            Pillar::mod(($this->dayPillar->stem % 5) * 2 + self::hourBranch($hour), 10),
            self::hourBranch($hour),
        );
    }

    public static function fromDate(DateTimeInterface|string $birthDate, ?int $hour = null, ?int $minute = null): self
    {
        return new self(CarbonImmutable::parse($birthDate), $hour, $minute);
    }

    /**
     * Chi của giờ sinh: 23:00-00:59 là Tý, 01:00-02:59 là Sửu…
     */
    public static function hourBranch(int $hour): int
    {
        return intdiv($hour + 1, 2) % 12;
    }

    /**
     * Can của tháng Dần theo phép Ngũ Hổ Độn (Giáp/Kỷ → Bính Dần…).
     */
    private static function tigerMonthStem(int $yearStem): int
    {
        return (($yearStem % 5) * 2 + 2) % 10;
    }

    /**
     * @return array<string, Pillar|null>
     */
    public function pillars(): array
    {
        return [
            'Năm' => $this->yearPillar,
            'Tháng' => $this->monthPillar,
            'Ngày' => $this->dayPillar,
            'Giờ' => $this->hourPillar,
        ];
    }

    /**
     * Mệnh theo nạp âm của năm sinh âm lịch, ví dụ "Kiếm Phong Kim".
     */
    public function destiny(): string
    {
        return $this->lunarDate->yearPillar()->napAm();
    }

    public function destinyElement(): Element
    {
        return $this->lunarDate->yearPillar()->napAmElement();
    }

    /**
     * Số lần mỗi hành xuất hiện trong các can chi của bát tự.
     *
     * @return array<string, int>
     */
    public function elementCounts(): array
    {
        $counts = array_fill_keys(array_map(fn (Element $element): string => $element->value, Element::cases()), 0);

        foreach (array_filter($this->pillars()) as $pillar) {
            $counts[$pillar->stemElement()->value]++;
            $counts[$pillar->branchElement()->value]++;
        }

        return $counts;
    }

    /**
     * Ngũ hành cục trong Tử vi, ví dụ "Kim Tứ Cục".
     */
    public function cuc(): ?string
    {
        $palace = $this->palacePillar($this->lifePalaceBranch());

        if ($palace === null) {
            return null;
        }

        $element = $palace->napAmElement();

        return $element->label().' '.['Nhị', 'Tam', 'Tứ', 'Ngũ', 'Lục'][$element->cucNumber() - 2].' Cục';
    }

    /**
     * Cung mà Thân cư ngụ: Mệnh, Phúc Đức, Quan Lộc, Thiên Di, Tài Bạch hoặc Phu Thê.
     */
    public function bodyPalace(): ?string
    {
        $life = $this->lifePalaceBranch();
        $body = $this->bodyPalaceBranch();

        if ($life === null || $body === null) {
            return null;
        }

        return self::PALACES[Pillar::mod($body - $life, 12)];
    }

    /**
     * Chi giờ sinh, ví dụ "Hợi".
     */
    public function hourBranchName(): ?string
    {
        return $this->hourPillar?->branchName();
    }

    /**
     * Tuổi mụ tại năm `$year` (mặc định năm nay).
     */
    public function age(?int $year = null): int
    {
        return ($year ?? now()->year) - $this->lunarDate->year + 1;
    }

    /**
     * An cung Mệnh: từ Dần đếm thuận đến tháng sinh, rồi đếm nghịch đến giờ sinh.
     */
    private function lifePalaceBranch(): ?int
    {
        if ($this->hour === null) {
            return null;
        }

        return Pillar::mod(2 + $this->lunarDate->month - 1 - self::hourBranch($this->hour), 12);
    }

    /**
     * An cung Thân: từ Dần đếm thuận đến tháng sinh, rồi đếm thuận đến giờ sinh.
     */
    private function bodyPalaceBranch(): ?int
    {
        if ($this->hour === null) {
            return null;
        }

        return Pillar::mod(2 + $this->lunarDate->month - 1 + self::hourBranch($this->hour), 12);
    }

    /**
     * Can chi của một cung, can lấy theo Ngũ Hổ Độn từ can năm âm lịch.
     */
    private function palacePillar(?int $branch): ?Pillar
    {
        if ($branch === null) {
            return null;
        }

        $yearStem = $this->lunarDate->yearPillar()->stem;

        return new Pillar(Pillar::mod(self::tigerMonthStem($yearStem) + Pillar::mod($branch - 2, 12), 10), $branch);
    }
}
