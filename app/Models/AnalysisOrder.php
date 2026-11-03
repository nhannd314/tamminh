<?php

namespace App\Models;

use App\AffiliateCommissionStatus;
use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\Support\ActivityFeed;
use Database\Factories\AnalysisOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
class AnalysisOrder extends Model
{
    /** @use HasFactory<AnalysisOrderFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (AnalysisOrder $order): void {
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
            'status' => AnalysisOrderStatus::class,
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
     * @return MorphOne<AffiliateCommission, $this>
     */
    public function affiliateCommission(): MorphOne
    {
        return $this->morphOne(AffiliateCommission::class, 'commissionable');
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
        return $this->status === AnalysisOrderStatus::Paid;
    }

    /**
     * Trọn gói mở mọi phần có phí còn viết được (bỏ các phần cần ảnh khi ảnh đã xóa).
     *
     * @return list<AnalysisSection>
     */
    public function sectionsToUnlock(): array
    {
        if (! $this->isFullPackage()) {
            return [$this->section];
        }

        return array_values(array_filter(AnalysisSection::paid(), fn (AnalysisSection $type): bool => $this->faceAnalysis->canGenerate($type)));
    }

    /**
     * Đánh dấu đã thanh toán và mở khoá nội dung. Gọi lại nhiều lần (webhook trùng) vẫn an toàn.
     *
     * @param  array<string, mixed>|null  $payload
     */
    public function markAsPaid(?string $transactionId = null, ?array $payload = null, ?string $paymentMethod = null): void
    {
        $justPaid = DB::transaction(function () use ($transactionId, $payload, $paymentMethod): bool {
            $order = static::query()->lockForUpdate()->findOrFail($this->getKey());

            if ($order->isPaid()) {
                return false;
            }

            $order->update([
                'status' => AnalysisOrderStatus::Paid,
                'payment_method' => $paymentMethod ?? $order->payment_method,
                'transaction_id' => $transactionId,
                'payment_payload' => $payload,
                'paid_at' => now(),
            ]);

            $order->faceAnalysis->unlock(...$order->sectionsToUnlock());

            // Luận giải là sản phẩm số không hoàn trả nên hoa hồng được xác nhận ngay khi nhận tiền.
            if ($order->faceAnalysis->affiliate !== null) {
                AffiliateCommission::record($order->faceAnalysis->affiliate, $order, $order->amount, Affiliate::analysisCommissionPercent(), AffiliateCommissionStatus::Approved);
            }

            return true;
        });

        $this->refresh();

        // Khách thường đang mở cửa sổ thanh toán: trang tự tải lại và stream nội dung, job chỉ chạy dự phòng.
        if ($justPaid) {
            ActivityFeed::announce('unlock', ActivityFeed::displayName($this->faceAnalysis->name).' vừa mở khoá luận giải đầy đủ');
            $this->faceAnalysis->generateContent($this->sectionsToUnlock(), now()->addMinutes(2));
        }
    }
}
