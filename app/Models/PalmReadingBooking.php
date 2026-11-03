<?php

namespace App\Models;

use App\Gender;
use App\PalmReadingPackage;
use Database\Factories\PalmReadingBookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Yêu cầu đặt lịch xem chỉ tay gửi từ trang chủ; `contacted_at` ghi lại lúc admin đã gọi lại khách.
 */
#[Fillable(['user_id', 'name', 'gender', 'birth_date', 'phone', 'package', 'contacted_at'])]
class PalmReadingBooking extends Model
{
    /** @use HasFactory<PalmReadingBookingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'birth_date' => 'date',
            'package' => PalmReadingPackage::class,
            'contacted_at' => 'datetime',
        ];
    }

    /**
     * Khách đặt lịch khi đã đăng nhập; null với khách vãng lai.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isContacted(): bool
    {
        return $this->contacted_at !== null;
    }
}
