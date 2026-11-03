<?php

namespace App\Models;

use App\AffiliateCommissionStatus;
use Database\Factories\AffiliateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cộng tác viên giới thiệu: khách hàng tự tham gia, nhận link `?ref={code}`.
 * Đơn vật phẩm và lượt xem tướng đến từ link được gắn `affiliate_id`, mỗi đơn đã trả tiền sinh một AffiliateCommission.
 * Admin chuyển khoản hoa hồng thủ công rồi ghi nhận bằng payOut(); `suspended_at` khoá tài khoản gian lận.
 */
#[Fillable(['code', 'bank_name', 'bank_account_number', 'bank_account_name', 'suspended_at'])]
class Affiliate extends Model
{
    /** @use HasFactory<AffiliateFactory> */
    use HasFactory;

    public const DEFAULT_COMMISSION_PERCENT = 10;

    public const DEFAULT_COOKIE_DAYS = 30;

    protected static function booted(): void
    {
        static::creating(function (Affiliate $affiliate): void {
            $affiliate->code ??= static::generateCode();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Mã 6 ký tự dễ đọc (bỏ 0/O, 1/I/L) để khách gõ lại được khi cần.
     */
    public static function generateCode(): string
    {
        do {
            $code = collect(range(1, 6))->map(fn (): string => Str::substr('ABCDEFGHJKMNPQRSTUVWXYZ23456789', random_int(0, 30), 1))->implode('');
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * % hoa hồng trên tiền hàng (không tính phí giao hàng) của đơn vật phẩm.
     */
    public static function productCommissionPercent(): int
    {
        return (int) Setting::get('affiliate.product_commission_percent', self::DEFAULT_COMMISSION_PERCENT);
    }

    /**
     * % hoa hồng trên số tiền khách trả để mở khoá luận giải xem tướng.
     */
    public static function analysisCommissionPercent(): int
    {
        return (int) Setting::get('affiliate.analysis_commission_percent', self::DEFAULT_COMMISSION_PERCENT);
    }

    /**
     * Số ngày link giới thiệu còn hiệu lực kể từ lần cuối khách bấm.
     */
    public static function cookieDays(): int
    {
        return max(1, (int) Setting::get('affiliate.cookie_days', self::DEFAULT_COOKIE_DAYS));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<AffiliateCommission, $this>
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function isActive(): bool
    {
        return $this->suspended_at === null;
    }

    public function hasBankAccount(): bool
    {
        return filled($this->bank_name) && filled($this->bank_account_number) && filled($this->bank_account_name);
    }

    /**
     * Link giới thiệu tới một trang của site (mặc định trang chủ).
     *
     * @param  array<string, mixed>  $parameters
     */
    public function referralUrl(string $routeName = 'home', array $parameters = [], string $fragment = ''): string
    {
        return route($routeName, [...$parameters, 'ref' => $this->code]).($fragment !== '' ? '#'.$fragment : '');
    }

    /**
     * Tổng hoa hồng theo trạng thái, đủ cả 4 trạng thái (0 khi chưa có).
     *
     * @return array<string, int>
     */
    public function totalsByStatus(): array
    {
        $sums = $this->commissions()->selectRaw('status, SUM(amount) as total')->groupBy('status')->pluck('total', 'status');

        return collect(AffiliateCommissionStatus::cases())
            ->mapWithKeys(fn (AffiliateCommissionStatus $status): array => [$status->value => (int) ($sums[$status->value] ?? 0)])
            ->all();
    }

    /**
     * Ghi nhận admin đã chuyển khoản toàn bộ hoa hồng đã xác nhận; trả về số tiền đã chi.
     */
    public function payOut(?string $note = null): int
    {
        return DB::transaction(function () use ($note): int {
            $commissions = $this->commissions()->where('status', AffiliateCommissionStatus::Approved)->lockForUpdate()->get();

            $this->commissions()->whereKey($commissions->modelKeys())->update([
                'status' => AffiliateCommissionStatus::Paid,
                'paid_at' => now(),
                'payout_note' => filled($note) ? trim($note) : null,
            ]);

            return (int) $commissions->sum('amount');
        });
    }
}
