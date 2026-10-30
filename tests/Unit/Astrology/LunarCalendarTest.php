<?php

use App\Support\Astrology\LunarCalendar;

test('converts solar dates to vietnamese lunar dates', function (string $solar, int $day, int $month, int $year, bool $isLeap) {
    [$y, $m, $d] = array_map('intval', explode('-', $solar));

    $lunar = LunarCalendar::toLunar($d, $m, $y);

    expect([$lunar->day, $lunar->month, $lunar->year, $lunar->isLeapMonth])->toBe([$day, $month, $year, $isLeap]);
})->with([
    'Tết Giáp Thìn' => ['2024-02-10', 1, 1, 2024, false],
    'Giao thừa Quý Mão' => ['2024-02-09', 30, 12, 2023, false],
    'Tết Quý Mão' => ['2023-01-22', 1, 1, 2023, false],
    'Tết 1985 (lịch Việt khác lịch Trung Quốc)' => ['1985-01-21', 1, 1, 1985, false],
    'Tháng 2 nhuận Quý Mão' => ['2023-03-22', 1, 2, 2023, true],
    'Tháng 4 nhuận Canh Tý' => ['2020-05-23', 1, 4, 2020, true],
    'Tết Canh Thìn' => ['2000-02-05', 1, 1, 2000, false],
]);

test('julian day number matches the reference epoch', function () {
    expect(LunarCalendar::julianDay(1, 1, 2000))->toBe(2451545);
});

test('sun longitude crosses lap xuan on 4 february 2024 afternoon', function () {
    $jd = LunarCalendar::julianDay(4, 2, 2024) - 0.5;

    expect(LunarCalendar::sunLongitude($jd + (10 - 7) / 24))->toBeLessThan(315.0)
        ->and(LunarCalendar::sunLongitude($jd + (18 - 7) / 24))->toBeGreaterThan(315.0);
});
