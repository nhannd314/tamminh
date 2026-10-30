<?php

namespace App\Models;

use App\AnalysisSection;
use App\AnalysisStatus;
use App\Gender;
use App\OrderStatus;
use Database\Factories\FaceAnalysisFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Một lượt xem tướng khuôn mặt: thông tin người dùng nhập + ảnh chân dung.
 * Truy cập qua URL chứa `token`; nội dung từng phần nằm ở FaceAnalysisSection.
 */
#[Fillable([
    'user_id', 'name', 'gender', 'birth_date', 'birth_hour', 'birth_minute', 'portrait_path', 'face_crop_path', 'face_metrics',
    'status', 'ai_model', 'failure_reason', 'analyzed_at', 'ip_address', 'terms_accepted_at',
])]
#[Hidden(['portrait_path', 'face_crop_path', 'ip_address'])]
class FaceAnalysis extends Model
{
    /** @use HasFactory<FaceAnalysisFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (FaceAnalysis $analysis): void {
            $analysis->token ??= Str::random(48);
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
            'gender' => Gender::class,
            'birth_date' => 'date',
            'birth_hour' => 'integer',
            'birth_minute' => 'integer',
            'face_metrics' => 'array',
            'status' => AnalysisStatus::class,
            'analyzed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<FaceAnalysisSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(FaceAnalysisSection::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Giờ sinh dạng "07:05", null khi không nhập giờ.
     *
     * @return Attribute<?string, never>
     */
    protected function birthTime(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->birth_hour === null
            ? null
            : sprintf('%02d:%02d', $this->birth_hour, $this->birth_minute ?? 0));
    }

    public function section(AnalysisSection $type): ?FaceAnalysisSection
    {
        return $this->sections->firstWhere('type', $type);
    }

    public function isUnlocked(AnalysisSection $type): bool
    {
        return $type->isFree() || (bool) $this->section($type)?->isUnlocked();
    }

    public function isFullyUnlocked(): bool
    {
        return $this->lockedSections() === [];
    }

    /**
     * @return list<AnalysisSection>
     */
    public function lockedSections(): array
    {
        return array_values(array_filter(AnalysisSection::paid(), fn (AnalysisSection $type): bool => ! $this->isUnlocked($type)));
    }

    /**
     * Mở khoá các phần đã thanh toán. Tạo sẵn bản ghi section (content = null) để job sinh nội dung điền vào.
     */
    public function unlock(AnalysisSection ...$types): void
    {
        foreach ($types as $type) {
            $section = $this->sections()->firstOrNew(['type' => $type]);
            $section->unlocked_at ??= now();
            $section->save();
        }

        $this->unsetRelation('sections');
    }

    /**
     * Giá trọn gói cho lượt xem này.
     *
     * Đã mua lẻ thì được trừ `pricing.upgrade_credit_percent` % số tiền đã trả cho các phần lẻ,
     * và không bao giờ đắt hơn tổng giá lẻ của các phần còn khoá.
     *
     * @return array{subtotal: int, discount: int, amount: int}
     */
    public function fullPackageQuote(): array
    {
        $subtotal = (int) Setting::get('pricing.full_package', 0);

        $paidForSections = (int) $this->orders()
            ->where('status', OrderStatus::Paid)
            ->whereNotNull('section')
            ->sum('amount');

        $credit = intdiv($paidForSections * (int) Setting::get('pricing.upgrade_credit_percent', 0), 100);
        $remaining = array_sum(array_map(fn (AnalysisSection $type): int => $type->price(), $this->lockedSections()));

        $amount = max(0, min($subtotal - $credit, $remaining));

        return ['subtotal' => $subtotal, 'discount' => $subtotal - $amount, 'amount' => $amount];
    }

    /**
     * Tạo đơn chờ thanh toán cho một phần lẻ, hoặc trọn gói khi `$section` = null.
     */
    public function createOrder(?AnalysisSection $section = null): Order
    {
        $quote = $section === null
            ? $this->fullPackageQuote()
            : ['subtotal' => $section->price(), 'discount' => 0, 'amount' => $section->price()];

        return $this->orders()->create([
            'section' => $section,
            'status' => OrderStatus::Pending,
            ...$quote,
        ]);
    }
}
