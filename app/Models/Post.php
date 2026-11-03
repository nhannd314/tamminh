<?php

namespace App\Models;

use App\PostTone;
use Database\Factories\PostFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Bài viết kiến thức. Chỉ hiện ra ngoài khi `published_at` đã tới (null = bản nháp, ngày tương lai = hẹn giờ đăng).
 * `content` là HTML từ RichEditor của Filament; ảnh chèn trong bài và ảnh bìa nằm trên disk `public`.
 */
#[Fillable([
    'post_category_id', 'title', 'slug', 'excerpt', 'content', 'cover_path', 'glyph', 'tone', 'author_name', 'meta_description', 'published_at',
])]
class Post extends Model implements HasRichContent
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use InteractsWithRichContent;

    /**
     * Tốc độ đọc tiếng Việt trung bình (âm tiết/phút), dùng để ước lượng "N phút đọc".
     */
    private const WORDS_PER_MINUTE = 220;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tone' => PostTone::class,
            'published_at' => 'datetime',
        ];
    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function readingMinutes(): int
    {
        $words = preg_split('/\s+/u', trim(strip_tags($this->content)), -1, PREG_SPLIT_NO_EMPTY);

        return max(1, (int) ceil(count($words) / self::WORDS_PER_MINUTE));
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    /**
     * Ngày đăng dạng "12 thg 5, 2026" như trên thẻ bài viết.
     */
    public function publishedDate(): string
    {
        return $this->published_at?->format('d \t\h\g n, Y') ?? 'Bản nháp';
    }

    /**
     * Dữ liệu cho <x-cards.post>.
     *
     * @return array{category: string, glyph: string, tone: string, title: string, excerpt: string, author: string, date: string, minutes: int, cover: ?string}
     */
    public function card(): array
    {
        return [
            'category' => $this->category->name,
            'glyph' => $this->glyph,
            'tone' => $this->tone->value,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'author' => $this->author_name,
            'date' => $this->publishedDate(),
            'minutes' => $this->readingMinutes(),
            'cover' => $this->coverUrl(),
        ];
    }
}
