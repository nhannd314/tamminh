<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Cấu hình hệ thống dạng key-value, `value` lưu JSON nên chứa được số, chuỗi, mảng.
 * Key đặt theo nhóm bằng dấu chấm: `pricing.full_package`, `analysis.free_sections`…
 */
#[Fillable(['key', 'value', 'description'])]
class Setting extends Model
{
    private const CACHE_KEY = 'settings';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::cached()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()->get()->pluck('value', 'key')->all());
    }
}
