<?php

namespace App\Models;

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\AnalysisStatus;
use App\Gender;
use App\Jobs\GenerateAnalysisSection;
use App\Support\AnalysisGenerator;
use Database\Factories\FaceAnalysisFactory;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Một lượt xem tướng khuôn mặt: thông tin người dùng nhập + ảnh chân dung.
 * Truy cập qua URL chứa `token`; nội dung từng phần nằm ở FaceAnalysisSection.
 * Ảnh chân dung bị xóa sau PORTRAIT_RETENTION_DAYS ngày (`portrait_path` = null), kết quả đã viết vẫn giữ.
 */
#[Fillable([
    'user_id', 'affiliate_id', 'name', 'gender', 'birth_date', 'birth_hour', 'birth_minute', 'portrait_path', 'face_crop_path', 'face_metrics',
    'status', 'ai_model', 'failure_reason', 'analyzed_at', 'ip_address', 'terms_accepted_at',
])]
#[Hidden(['portrait_path', 'face_crop_path', 'ip_address'])]
class FaceAnalysis extends Model
{
    /** @use HasFactory<FaceAnalysisFactory> */
    use HasFactory;

    /**
     * Số ngày giữ ảnh chân dung, sau đó lệnh `analyses:purge-portraits` xóa ảnh.
     */
    public const PORTRAIT_RETENTION_DAYS = 15;

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
     * Affiliate giới thiệu lượt xem; nhận hoa hồng mọi đơn mở khoá đã thanh toán của lượt xem này.
     *
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * @return HasMany<FaceAnalysisSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(FaceAnalysisSection::class);
    }

    /**
     * @return HasMany<AnalysisOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(AnalysisOrder::class);
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

    public function hasPortrait(): bool
    {
        return $this->portrait_path !== null;
    }

    /**
     * Xóa ảnh gốc và ảnh cắt khỏi ổ đĩa; số đo khuôn mặt và nội dung đã viết vẫn giữ.
     */
    public function deletePortrait(): void
    {
        Storage::disk('local')->delete(array_filter([$this->portrait_path, $this->face_crop_path]));

        $this->update(['portrait_path' => null, 'face_crop_path' => null]);
    }

    /**
     * Còn đủ dữ liệu để viết phần này: phần cần ảnh thì ảnh chưa bị xóa.
     */
    public function canGenerate(AnalysisSection $type): bool
    {
        return $this->hasPortrait() || ! $type->needsPortrait();
    }

    /**
     * Các phần còn khoá mà vẫn bán được (viết được nội dung), theo thứ tự hiển thị.
     *
     * @return list<AnalysisSection>
     */
    public function purchasableSections(): array
    {
        return array_values(array_filter($this->lockedSections(), fn (AnalysisSection $type): bool => $this->canGenerate($type)));
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
     * Đưa vào hàng đợi việc sinh nội dung (Gemini) cho các phần đã mở khoá mà chưa có nội dung.
     * `$delay` dùng khi trang kết quả sẽ tự stream nội dung, job chỉ chạy dự phòng nếu khách không mở trang.
     *
     * @param  list<AnalysisSection>|AnalysisSection  $types
     */
    public function generateContent(array|AnalysisSection $types, ?DateTimeInterface $delay = null): void
    {
        $pending = array_filter(Arr::wrap($types), fn (AnalysisSection $type): bool => AnalysisGenerator::supports($type)
            && ! $this->section($type)?->isGenerated());

        if ($pending === []) {
            return;
        }

        $this->update(['status' => AnalysisStatus::Processing, 'failure_reason' => null]);

        foreach ($pending as $type) {
            GenerateAnalysisSection::dispatch($this, $type)->delay($delay);
        }
    }

    /**
     * Các phần đã mở khoá (và sinh được nội dung) nhưng chưa có nội dung, theo thứ tự hiển thị.
     *
     * @return list<AnalysisSection>
     */
    public function pendingSections(): array
    {
        return array_values(array_filter(AnalysisSection::cases(), fn (AnalysisSection $type): bool => AnalysisGenerator::supports($type)
            && $this->isUnlocked($type)
            && ! $this->section($type)?->isGenerated()));
    }

    public function hasPendingContent(): bool
    {
        return $this->pendingSections() !== [];
    }

    /**
     * Còn nội dung đang chờ sinh và chưa thất bại hẳn.
     */
    public function isGenerating(): bool
    {
        return $this->status !== AnalysisStatus::Failed && $this->hasPendingContent();
    }

    /**
     * Khoá để luồng stream trên trang kết quả và job dự phòng không cùng gọi Gemini cho một lượt xem.
     */
    public function generationLock(): Lock
    {
        return Cache::lock("face-analysis:{$this->getKey()}:generation", 600);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    public function saveGeneratedContent(AnalysisSection $type, array $content, string $model): void
    {
        $this->sections()->updateOrCreate(['type' => $type], ['content' => $content, 'generated_at' => now()]);
        $this->unsetRelation('sections');

        $this->update([
            'status' => $this->hasPendingContent() ? AnalysisStatus::Processing : AnalysisStatus::Completed,
            'ai_model' => $model,
            'analyzed_at' => now(),
            'failure_reason' => null,
        ]);
    }

    public function markGenerationFailed(AnalysisSection $type, ?Throwable $exception): void
    {
        $this->update([
            'status' => AnalysisStatus::Failed,
            'failure_reason' => Str::limit("[{$type->value}] ".$exception?->getMessage(), 1000),
        ]);
    }

    /**
     * Giá trọn gói cho lượt xem này: tổng giá lẻ các phần có phí, trừ `pricing.full_package_discount_percent` %.
     *
     * Phần đã mua lẻ được khấu trừ đúng giá của nó trong trọn gói (giá lẻ × (100% - chiết khấu)),
     * nên số tiền phải trả là tổng giá trọn gói của các phần còn khoá. Phần không còn bán (ảnh đã xóa) cũng được trừ ra.
     *
     * @return array{subtotal: int, discount: int, amount: int}
     */
    public function fullPackageQuote(): array
    {
        $subtotal = array_sum(array_map(fn (AnalysisSection $type): int => $type->price(), AnalysisSection::paid()));
        $amount = array_sum(array_map(fn (AnalysisSection $type): int => $type->bundledPrice(), $this->purchasableSections()));

        return ['subtotal' => $subtotal, 'discount' => $subtotal - $amount, 'amount' => $amount];
    }

    /**
     * Tạo đơn chờ thanh toán cho một phần lẻ, hoặc trọn gói khi `$section` = null.
     */
    public function createOrder(?AnalysisSection $section = null): AnalysisOrder
    {
        $quote = $section === null
            ? $this->fullPackageQuote()
            : ['subtotal' => $section->price(), 'discount' => 0, 'amount' => $section->price()];

        return $this->orders()->create([
            'section' => $section,
            'status' => AnalysisOrderStatus::Pending,
            ...$quote,
        ]);
    }
}
