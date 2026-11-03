<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * `role` cố ý không nằm trong Fillable để form đăng ký không thể tự gán quyền admin;
 * gán trực tiếp: `$user->role = UserRole::Admin`.
 */
#[Fillable(['name', 'email', 'phone', 'address', 'newsletter_opt_in', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'newsletter_opt_in' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    /**
     * Chữ cái đại diện cho avatar: chữ đầu của tên (từ cuối trong họ tên), không có tên thì lấy chữ đầu email.
     *
     * @return Attribute<string, never>
     */
    protected function avatarInitial(): Attribute
    {
        return Attribute::get(function (): string {
            $givenName = Str::of((string) $this->name)->squish()->explode(' ')->last();
            $source = filled($givenName) ? $givenName : (string) $this->email;

            return mb_strtoupper(mb_substr($source, 0, 1)) ?: '?';
        });
    }

    /**
     * Tài khoản tạo bằng Google không có mật khẩu cho tới khi khách tự đặt.
     */
    public function hasPassword(): bool
    {
        return filled($this->password);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Chỉ admin được vào trang quản trị Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return HasMany<FaceAnalysis, $this>
     */
    public function faceAnalyses(): HasMany
    {
        return $this->hasMany(FaceAnalysis::class);
    }

    /**
     * @return HasOne<Affiliate, $this>
     */
    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<PalmReadingBooking, $this>
     */
    public function palmReadingBookings(): HasMany
    {
        return $this->hasMany(PalmReadingBooking::class);
    }
}
