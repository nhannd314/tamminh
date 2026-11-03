<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Trang nội dung tĩnh (điều khoản, bảo mật, liên hệ…), hiển thị tại `/{slug}`.
 * `content` là HTML từ RichEditor của Filament.
 */
#[Fillable(['title', 'slug', 'summary', 'content', 'meta_description', 'is_published'])]
class Page extends Model implements HasRichContent
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use InteractsWithRichContent;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
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
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }
}
