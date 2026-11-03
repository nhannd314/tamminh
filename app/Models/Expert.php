<?php

namespace App\Models;

use Database\Factories\ExpertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Thầy/chuyên gia hiển thị ngoài trang chủ, truy cập qua `slug`.
 * `services` là [{name, description, duration, price}], `reviews` là [{rating, quote, name, location}], `specialties` là danh sách nhãn.
 * `user_id` để trống khi chuyên gia chưa có tài khoản; số điện thoại cũng là số Zalo để nhắn tin.
 */
#[Fillable([
    'user_id', 'name', 'slug', 'glyph', 'title', 'headline', 'photo_path', 'years_experience', 'sessions_count', 'rating', 'reviews_count',
    'email', 'phone', 'address', 'working_hours', 'office_image_path', 'description', 'specialties', 'services', 'reviews',
    'tiktok_url', 'facebook_url', 'youtube_url', 'is_active', 'is_accepting_bookings', 'sort',
])]
class Expert extends Model
{
    /** @use HasFactory<ExpertFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_accepting_bookings' => 'boolean',
            'rating' => 'decimal:1',
            'specialties' => 'array',
            'services' => 'array',
            'reviews' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Ảnh đã tải lên, nếu chưa có thì dùng ảnh mặc định (null khi cũng không có ảnh mặc định).
     */
    public function photoUrl(): ?string
    {
        if ($this->photo_path) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return file_exists(public_path('images/thay-tuong-so.png')) ? asset('images/thay-tuong-so.png') : null;
    }

    public function officeImageUrl(): ?string
    {
        return $this->office_image_path ? Storage::disk('public')->url($this->office_image_path) : null;
    }

    /**
     * Link chỉ đường Google Maps theo địa chỉ văn phòng.
     */
    public function mapUrl(): ?string
    {
        return filled($this->address) ? 'https://maps.google.com/?q='.urlencode($this->address) : null;
    }

    /**
     * Link nhắn tin Zalo theo số điện thoại; đổi đầu 0 sang mã vùng 84 như Zalo yêu cầu.
     */
    public function zaloUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if ($digits === '') {
            return null;
        }

        return 'https://zalo.me/'.(str_starts_with($digits, '0') ? '84'.substr($digits, 1) : $digits);
    }

    /**
     * Các kênh mạng xã hội đã khai báo, dạng [network => url].
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        return array_filter([
            'tiktok' => $this->tiktok_url,
            'facebook' => $this->facebook_url,
            'youtube' => $this->youtube_url,
        ]);
    }
}
