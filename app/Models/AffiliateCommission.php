<?php

namespace App\Models;

use App\AffiliateCommissionStatus;
use Database\Factories\AffiliateCommissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Hoa hồng của một đơn giới thiệu, chốt tỉ lệ và số tiền tại lúc ghi nhận.
 * Đơn vật phẩm: chờ xác nhận khi đặt, xác nhận khi đơn Hoàn tất, huỷ theo đơn.
 * Đơn xem tướng: xác nhận ngay khi khách thanh toán (sản phẩm số, không hoàn trả).
 */
#[Fillable(['affiliate_id', 'commissionable_type', 'commissionable_id', 'base_amount', 'rate_percent', 'amount', 'status', 'approved_at', 'paid_at', 'payout_note'])]
class AffiliateCommission extends Model
{
    /** @use HasFactory<AffiliateCommissionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_amount' => 'integer',
            'rate_percent' => 'integer',
            'amount' => 'integer',
            'status' => AffiliateCommissionStatus::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Ghi hoa hồng cho một đơn; gọi lại cho cùng đơn (webhook trùng) không tạo thêm bản ghi.
     * Bỏ qua khi affiliate đã bị khoá hoặc số tiền hoa hồng bằng 0.
     */
    public static function record(Affiliate $affiliate, Order|AnalysisOrder $source, int $baseAmount, int $ratePercent, AffiliateCommissionStatus $status): ?self
    {
        $amount = intdiv($baseAmount * $ratePercent, 100);

        if (! $affiliate->isActive() || $amount <= 0) {
            return null;
        }

        return static::query()->firstOrCreate(
            ['commissionable_type' => $source->getMorphClass(), 'commissionable_id' => $source->getKey()],
            [
                'affiliate_id' => $affiliate->getKey(),
                'base_amount' => $baseAmount,
                'rate_percent' => $ratePercent,
                'amount' => $amount,
                'status' => $status,
                'approved_at' => $status === AffiliateCommissionStatus::Approved ? now() : null,
            ],
        );
    }

    /**
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function commissionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approve(): void
    {
        if ($this->status === AffiliateCommissionStatus::Pending) {
            $this->update(['status' => AffiliateCommissionStatus::Approved, 'approved_at' => now()]);
        }
    }

    /**
     * Huỷ khoản chưa chi trả; khoản đã chi trả giữ nguyên để admin tự đối soát.
     */
    public function cancel(): void
    {
        if (in_array($this->status, [AffiliateCommissionStatus::Pending, AffiliateCommissionStatus::Approved], true)) {
            $this->update(['status' => AffiliateCommissionStatus::Cancelled]);
        }
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [AffiliateCommissionStatus::Pending, AffiliateCommissionStatus::Approved], true);
    }

    /**
     * Nguồn hoa hồng cho người đọc, không lộ thông tin khách mua: "Vật phẩm · DH…" / "Xem tướng · TM…".
     */
    public function sourceLabel(): string
    {
        $source = $this->commissionable;

        return match (true) {
            $source instanceof Order => 'Vật phẩm · '.$source->code,
            $source instanceof AnalysisOrder => 'Xem tướng · '.$source->code,
            default => 'Đơn đã xoá',
        };
    }
}
