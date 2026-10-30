<?php

use App\Support\Astrology\BirthChart;
use App\Support\Astrology\Element;

test('day pillar follows the sexagenary day cycle', function (string $date, string $pillar) {
    expect(BirthChart::fromDate($date, 12)->dayPillar->name())->toBe($pillar);
})->with([
    ['2000-01-01', 'Mậu Ngọ'],
    ['2024-02-10', 'Giáp Thìn'],
]);

test('year and month pillars change at lap xuan rather than tet', function () {
    $before = BirthChart::fromDate('2024-02-04', 10);
    $after = BirthChart::fromDate('2024-02-04', 18);

    expect($before->yearPillar->name())->toBe('Quý Mão')
        ->and($before->monthPillar->name())->toBe('Ất Sửu')
        ->and($after->yearPillar->name())->toBe('Giáp Thìn')
        ->and($after->monthPillar->name())->toBe('Bính Dần')
        ->and($after->lunarDate->year)->toBe(2023);
});

test('builds the full chart for 15/08/1992 21:30', function () {
    $chart = BirthChart::fromDate('1992-08-15', 21, 30);

    expect($chart->yearPillar->name())->toBe('Nhâm Thân')
        ->and($chart->monthPillar->name())->toBe('Mậu Thân')
        ->and($chart->dayPillar->name())->toBe('Quý Hợi')
        ->and($chart->hourPillar->name())->toBe('Quý Hợi')
        ->and($chart->hourBranchName())->toBe('Hợi')
        ->and($chart->destiny())->toBe('Kiếm Phong Kim')
        ->and($chart->destinyElement())->toBe(Element::Kim)
        ->and($chart->age(2026))->toBe(35);
});

test('late tý hour belongs to the next day', function () {
    $chart = BirthChart::fromDate('2000-01-01', 23, 15);

    expect($chart->dayPillar->name())->toBe('Kỷ Mùi')
        ->and($chart->hourPillar->name())->toBe('Giáp Tý');
});

test('counts the five elements across the eight characters', function () {
    $counts = BirthChart::fromDate('1992-08-15', 21, 30)->elementCounts();

    expect(array_sum($counts))->toBe(8)
        ->and($counts['thuy'])->toBe(5);
});

test('body palace only falls on the six allowed palaces', function () {
    foreach (range(0, 23) as $hour) {
        expect(BirthChart::fromDate('1992-08-15', $hour)->bodyPalace())
            ->toBeIn(['Mệnh', 'Phúc Đức', 'Quan Lộc', 'Thiên Di', 'Tài Bạch', 'Phu Thê']);
    }
});

test('ty and ngo hours put the body palace in the life palace', function () {
    expect(BirthChart::fromDate('1992-08-15', 0)->bodyPalace())->toBe('Mệnh')
        ->and(BirthChart::fromDate('1992-08-15', 12)->bodyPalace())->toBe('Mệnh');
});

test('cuc follows the nap am of the life palace', function () {
    // Năm Giáp (cung Dần = Bính Dần), tháng Giêng giờ Tý → Mệnh tại Dần: Bính Dần = Lư Trung Hỏa → Hỏa Lục Cục.
    expect(BirthChart::fromDate('2024-02-12', 0)->cuc())->toBe('Hỏa Lục Cục');
});

test('chart without birth time has no hour pillar, cuc or body palace', function () {
    $chart = BirthChart::fromDate('1992-08-15');

    expect($chart->hourPillar)->toBeNull()
        ->and($chart->cuc())->toBeNull()
        ->and($chart->bodyPalace())->toBeNull()
        ->and(array_sum($chart->elementCounts()))->toBe(6);
});
