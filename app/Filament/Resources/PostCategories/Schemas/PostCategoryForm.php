<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Models\PostCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Các trường của chuyên mục, dùng cho trang quản lý chuyên mục và nút tạo nhanh trong form bài viết.
 */
class PostCategoryForm
{
    /**
     * @return list<Component>
     */
    public static function components(): array
    {
        return [
            TextInput::make('name')
                ->label('Tên chuyên mục')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?PostCategory $record): void {
                    if ($record === null && blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')
                ->label('Đường dẫn')
                ->prefix('/bai-viet/chuyen-muc/')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(PostCategory::class, ignoreRecord: true),
            Textarea::make('description')
                ->label('Mô tả')
                ->helperText('Hiện dưới tiêu đề trang chuyên mục.')
                ->rows(2)
                ->maxLength(255),
            TextInput::make('sort')
                ->label('Thứ tự')
                ->numeric()
                ->minValue(0)
                ->default(0),
        ];
    }
}
