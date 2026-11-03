<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Thông báo hoạt động hiển thị cho mọi khách đang mở website (kênh công khai `activity`).
 * Phát ngay (không qua queue) để thông báo tới nơi kịp thời.
 */
class ActivityOccurred implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public string $kind,
        public string $message,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('activity')];
    }

    public function broadcastAs(): string
    {
        return 'activity.occurred';
    }
}
