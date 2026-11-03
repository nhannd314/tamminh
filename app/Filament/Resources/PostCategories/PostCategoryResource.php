<?php

namespace App\Filament\Resources\PostCategories;

use App\Filament\Resources\PostCategories\Pages\ManagePostCategories;
use App\Filament\Resources\PostCategories\Schemas\PostCategoryForm;
use App\Models\PostCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'chuyên mục';

    protected static ?string $pluralModelLabel = 'Chuyên mục';

    protected static ?string $slug = 'chuyen-muc';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(PostCategoryForm::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên chuyên mục')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->color('gray'),
                TextColumn::make('posts_count')
                    ->label('Số bài')
                    ->counts('posts')
                    ->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
                // Bảng posts ràng buộc restrictOnDelete: chỉ xoá được chuyên mục chưa có bài.
                DeleteAction::make()
                    ->hidden(fn (PostCategory $record): bool => $record->posts()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePostCategories::route('/'),
        ];
    }
}
