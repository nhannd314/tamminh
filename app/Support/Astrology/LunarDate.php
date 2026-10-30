<?php

namespace App\Support\Astrology;

/**
 * Ngày âm lịch Việt Nam.
 */
final readonly class LunarDate
{
    public function __construct(
        public int $day,
        public int $month,
        public int $year,
        public bool $isLeapMonth = false,
    ) {}

    /**
     * Can chi của năm âm lịch (tính theo Tết).
     */
    public function yearPillar(): Pillar
    {
        return Pillar::ofYear($this->year);
    }

    /**
     * Ví dụ: "18 tháng 7 năm Nhâm Thân", "1 tháng 2 nhuận năm Quý Mão".
     */
    public function format(): string
    {
        return sprintf(
            '%d tháng %d%s năm %s',
            $this->day,
            $this->month,
            $this->isLeapMonth ? ' nhuận' : '',
            $this->yearPillar()->name(),
        );
    }
}
