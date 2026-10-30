<?php

namespace App\Models;

use App\AnalysisSection;
use App\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Đơn mua một phần (`section`) hoặc trọn gói (`section` = null) của một FaceAnalysis.
 * Tiền tính bằng đồng: `amount` = `subtotal` - `discount`, chốt tại thời điểm tạo đơn.
 */
#[Fillable([
    'section', 'subtotal', 'discount', 'amount', 'currency', 'status',
    'payment_method', 'transaction_id', 'payment_payload', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->code ??= 'TM'.Str::upper(Str::random(8));
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
            'section' => AnalysisSection::class,
            'subtotal' => 'integer',
            'discount' => 'integer',
            'amount' => 'integer',
            'status' => OrderStatus::class,
            'payment_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<FaceAnalysis, $this>
     */
    public function faceAnalysis(): BelongsTo
    {
        return $this->belongsTo(FaceAnalysis::class);
    }

    /**
     * Tìm mã đơn (TM + 8 ký tự) trong nội dung chuyển khoản; ngân hàng có thể thêm chữ trước/sau.
     */
    public static function findCodeIn(string $text): ?string
    {
        return preg_match('/TM[A-Z0-9]{8}/', Str::upper($text), $matches) === 1 ? $matches[0] : null;
    }

    public function isFullPackage(): bool
    {
        return $this->section === null;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    /**
     * @return list<AnalysisSection>
     */
    public function sectionsToUnlock(): array
    {
        return $this->isFullPackage() ? AnalysisSection::paid() : [$this->section];
    }

    /**
     * Đánh dấu đã thanh toán và mở khoá nội dung. Gọi lại nhiều lần (webhook trùng) vẫn an toàn.
     *
     * @param  array<string, mixed>|null  $payload
     */
    public function markAsPaid(?string $transactionId = null, ?array $payload = null, ?string $paymentMethod = null): void
    {
        DB::transaction(function () use ($transactionId, $payload, $paymentMethod): void {
            $order = static::query()->lockForUpdate()->findOrFail($this->getKey());

            if ($order->isPaid()) {
                return;
            }

            $order->update([
                'status' => OrderStatus::Paid,
                'payment_method' => $paymentMethod ?? $order->payment_method,
                'transaction_id' => $transactionId,
                'payment_payload' => $payload,
                'paid_at' => now(),
            ]);

            $order->faceAnalysis->unlock(...$order->sectionsToUnlock());
        });

        $this->refresh();
    }
}
