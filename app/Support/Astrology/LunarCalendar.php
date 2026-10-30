<?php

namespace App\Support\Astrology;

/**
 * Âm lịch Việt Nam theo thuật toán thiên văn của Hồ Ngọc Đức (múi giờ UTC+7).
 * Độ chính xác tốt trong khoảng năm 1800-2199.
 *
 * @see https://www.informatik.uni-leipzig.de/~duc/amlich/calrules.html
 */
final class LunarCalendar
{
    public const TIME_ZONE = 7.0;

    private const SYNODIC_MONTH = 29.530588853;

    private const NEW_MOON_EPOCH = 2415021.076998695;

    /**
     * Julian Day Number của một ngày dương lịch (lịch Gregory, Julius trước 15/10/1582).
     */
    public static function julianDay(int $day, int $month, int $year): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;

        $jd = $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;

        if ($jd < 2299161) {
            $jd = $day + intdiv(153 * $m + 2, 5) + 365 * $y + intdiv($y, 4) - 32083;
        }

        return $jd;
    }

    public static function toLunar(int $day, int $month, int $year): LunarDate
    {
        $dayNumber = self::julianDay($day, $month, $year);
        $k = (int) floor(($dayNumber - self::NEW_MOON_EPOCH) / self::SYNODIC_MONTH);

        $monthStart = self::newMoonDay($k + 1);
        if ($monthStart > $dayNumber) {
            $monthStart = self::newMoonDay($k);
        }

        $a11 = self::lunarMonth11($year);
        $b11 = $a11;

        if ($a11 >= $monthStart) {
            $lunarYear = $year;
            $a11 = self::lunarMonth11($year - 1);
        } else {
            $lunarYear = $year + 1;
            $b11 = self::lunarMonth11($year + 1);
        }

        $lunarDay = $dayNumber - $monthStart + 1;
        $diff = (int) floor(($monthStart - $a11) / 29);
        $isLeap = false;
        $lunarMonth = $diff + 11;

        if ($b11 - $a11 > 365) {
            $leapMonthDiff = self::leapMonthOffset($a11);

            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                $isLeap = $diff === $leapMonthDiff;
            }
        }

        if ($lunarMonth > 12) {
            $lunarMonth -= 12;
        }

        if ($lunarMonth >= 11 && $diff < 4) {
            $lunarYear--;
        }

        return new LunarDate($lunarDay, $lunarMonth, $lunarYear, $isLeap);
    }

    /**
     * Kinh độ mặt trời (độ, 0-360) tại thời điểm Julian Day `$jd` theo giờ UT.
     */
    public static function sunLongitude(float $jd): float
    {
        $t = ($jd - 2451545.0) / 36525;
        $t2 = $t * $t;
        $dr = M_PI / 180;

        $meanAnomaly = 357.52910 + 35999.05030 * $t - 0.0001559 * $t2 - 0.00000048 * $t * $t2;
        $meanLongitude = 280.46645 + 36000.76983 * $t + 0.0003032 * $t2;

        $equation = (1.914600 - 0.004817 * $t - 0.000014 * $t2) * sin($dr * $meanAnomaly)
            + (0.019993 - 0.000101 * $t) * sin($dr * 2 * $meanAnomaly)
            + 0.000290 * sin($dr * 3 * $meanAnomaly);

        return fmod(fmod($meanLongitude + $equation, 360) + 360, 360);
    }

    /**
     * Ngày (Julian Day Number, giờ Việt Nam) của lần sóc thứ `$k` kể từ 1/1/1900.
     */
    private static function newMoonDay(int $k): int
    {
        return (int) floor(self::newMoon($k) + 0.5 + self::TIME_ZONE / 24);
    }

    private static function newMoon(int $k): float
    {
        $t = $k / 1236.85;
        $t2 = $t * $t;
        $t3 = $t2 * $t;
        $dr = M_PI / 180;

        $jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $t2 - 0.000000155 * $t3
            + 0.00033 * sin((166.56 + 132.87 * $t - 0.009173 * $t2) * $dr);

        $m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;
        $mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;

        $c1 = (0.1734 - 0.000393 * $t) * sin($m * $dr) + 0.0021 * sin(2 * $dr * $m)
            - 0.4068 * sin($mpr * $dr) + 0.0161 * sin($dr * 2 * $mpr)
            - 0.0004 * sin($dr * 3 * $mpr)
            + 0.0104 * sin($dr * 2 * $f) - 0.0051 * sin($dr * ($m + $mpr))
            - 0.0074 * sin($dr * ($m - $mpr)) + 0.0004 * sin($dr * (2 * $f + $m))
            - 0.0004 * sin($dr * (2 * $f - $m)) - 0.0006 * sin($dr * (2 * $f + $mpr))
            + 0.0010 * sin($dr * (2 * $f - $mpr)) + 0.0005 * sin($dr * (2 * $mpr + $m));

        $deltaT = $t < -11
            ? 0.001 + 0.000839 * $t + 0.0002261 * $t2 - 0.00000845 * $t3 - 0.000000081 * $t * $t3
            : -0.000278 + 0.000265 * $t + 0.000262 * $t2;

        return $jd1 + $c1 - $deltaT;
    }

    /**
     * Cung hoàng đạo chính (0-11, mỗi cung 30°) mà mặt trời đang ở vào đầu ngày `$dayNumber`.
     */
    private static function sunSegment(int $dayNumber): int
    {
        return (int) floor(self::sunLongitude($dayNumber - 0.5 - self::TIME_ZONE / 24) / 30);
    }

    /**
     * Ngày bắt đầu tháng 11 âm lịch (tháng chứa Đông chí) của năm dương lịch `$year`.
     */
    private static function lunarMonth11(int $year): int
    {
        $offset = self::julianDay(31, 12, $year) - 2415021;
        $k = (int) floor($offset / self::SYNODIC_MONTH);
        $newMoon = self::newMoonDay($k);

        if (self::sunSegment($newMoon) >= 9) {
            $newMoon = self::newMoonDay($k - 1);
        }

        return $newMoon;
    }

    /**
     * Vị trí tháng nhuận tính từ tháng 11 âm lịch `$a11` (tháng đầu tiên không chứa trung khí).
     */
    private static function leapMonthOffset(int $a11): int
    {
        $k = (int) floor(($a11 - self::NEW_MOON_EPOCH) / self::SYNODIC_MONTH + 0.5);
        $i = 1;
        $arc = self::sunSegment(self::newMoonDay($k + $i));

        do {
            $last = $arc;
            $i++;
            $arc = self::sunSegment(self::newMoonDay($k + $i));
        } while ($arc !== $last && $i < 14);

        return $i - 1;
    }
}
