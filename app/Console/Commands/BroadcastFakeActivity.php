<?php

namespace App\Console\Commands;

use App\Support\ActivityFeed;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('activity:fake {--force : Phát ngay, bỏ qua khoảng chờ ngẫu nhiên}')]
#[Description('Phát một thông báo hoạt động giả ngẫu nhiên (scheduler gọi mỗi phút, lệnh tự giãn cách vài phút)')]
class BroadcastFakeActivity extends Command
{
    private const NEXT_RUN_CACHE_KEY = 'activity.fake.next_at';

    public function handle(): int
    {
        if (! config('activity.fake_enabled')) {
            return self::SUCCESS;
        }

        $nextAt = Cache::get(self::NEXT_RUN_CACHE_KEY);

        if (! $this->option('force') && $nextAt !== null && now()->timestamp < $nextAt) {
            return self::SUCCESS;
        }

        $activity = ActivityFeed::fake();
        ActivityFeed::announce($activity['kind'], $activity['message']);

        ['min' => $min, 'max' => $max] = config('activity.fake_interval_minutes');
        Cache::forever(self::NEXT_RUN_CACHE_KEY, now()->addSeconds(random_int($min * 60, max($min, $max) * 60))->timestamp);

        $this->info($activity['message']);

        return self::SUCCESS;
    }
}
