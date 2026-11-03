<?php

namespace App\Models;

use App\AffiliateCommissionStatus;
use App\OrderPaymentMethod;
use App\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Đơn vật phẩm phong thủy đặt từ giỏ hàng, truy cập qua `code` (DH + 8 ký tự).
 * Tiền tính bằng đồng: `total` = `subtotal` + `shipping_fee`, chốt tại lúc đặt.
 * Đơn online cần chuyển khoản qua SePay (webhook ghi `paid_at`); đơn COD thu tiền khi giao.
 */
#[Fillable([
    'user_id', 'affiliate_id', 'customer_name', 'phone', 'address', 'note', 'payment_method', 'subtotal', 'shipping_fee', 'total',
    'status', 'transaction_id', 'payment_payload', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->code ??= 'DH'.Str::upper(Str::random(8));
        });

        // Hoa hồng tính trên tiền hàng (không gồm phí giao), chờ tới khi đơn Hoàn tất mới được xác nhận.
        static::created(function (Order $order): void {
            if ($order->affiliate !== null) {
                AffiliateCommission::record($order->affiliate, $order, $order->subtotal, Affiliate::productCommissionPercent(), AffiliateCommissionStatus::Pending);
            }
        });

        static::updated(function (Order $order): void {
            if (! $order->wasChanged('status')) {
                return;
            }

            match ($order->status) {
                OrderStatus::Completed => $order->affiliateCommission?->approve(),
                OrderStatus::Cancelled => $order->affiliateCommission?->cancel(),
                default => null,
            };
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
            'payment_method' => OrderPaymentMethod::class,
            'status' => OrderStatus::class,
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'total' => 'integer',
            'payment_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Khách đặt hàng khi đã đăng nhập; null với đơn của khách vãng lai.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Affiliate giới thiệu đơn này (link `?ref=` còn hiệu lực lúc đặt).
     *
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * @return MorphOne<AffiliateCommission, $this>
     */
    public function affiliateCommission(): MorphOne
    {
        return $this->morphOne(AffiliateCommission::class, 'commissionable');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Số tiền khách cần chuyển khoản; PaymentInstructions dùng chung với đơn xem tướng.
     */
    public function getAmountAttribute(): int
    {
        return $this->total;
    }

    /**
     * Tìm mã đơn vật phẩm (DH + 8 ký tự) trong nội dung chuyển khoản.
     */
    public static function findCodeIn(string $text): ?string
    {
        return preg_match('/DH[A-Z0-9]{8}/', Str::upper($text), $matches) === 1 ? $matches[0] : null;
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    /**
     * Đơn online chưa nhận tiền và chưa huỷ thì còn chờ khách chuyển khoản.
     */
    public function awaitsOnlinePayment(): bool
    {
        return $this->payment_method === OrderPaymentMethod::Online && ! $this->isPaid() && ! $this->isCancelled();
    }

    /**
     * Ghi nhận đã thanh toán. Gọi lại nhiều lần (webhook trùng) vẫn an toàn.
     *
     * @param  array<string, mixed>|null  $payload
     */
    public function markAsPaid(?string $transactionId = null, ?array $payload = null): void
    {
        DB::transaction(function () use ($transactionId, $payload): void {
            $order = static::query()->lockForUpdate()->findOrFail($this->getKey());

            if ($order->isPaid()) {
                return;
            }

            $order->update([
                'transaction_id' => $transactionId,
                'payment_payload' => $payload,
                'paid_at' => now(),
            ]);
        });

        $this->refresh();
    }

    /**
     * Huỷ đơn và trả lại tồn kho đã giữ khi đặt; đơn đã huỷ thì bỏ qua.
     */
    public function cancel(): void
    {
        DB::transaction(function (): void {
            $order = static::query()->with('items')->lockForUpdate()->findOrFail($this->getKey());

            if ($order->isCancelled()) {
                return;
            }

            foreach ($order->items as $item) {
                if ($item->product_id !== null) {
                    Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
        });

        $this->refresh();
    }
}
