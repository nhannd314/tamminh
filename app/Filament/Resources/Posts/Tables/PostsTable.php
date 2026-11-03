<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Tiêu đề')
                    ->searchable()
                    ->limit(70)
                    ->description(fn (Post $record): string => '/bai-viet/'.$record->slug),
                TextColumn::make('category.name')
                    ->label('Chuyên mục')
                    ->badge(),
                TextColumn::make('author_name')
                    ->label('Tác giả')
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Ngày đăng')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Bản nháp')
                    ->color(fn (Post $record): ?string => $record->isPublished() ? null : 'warning')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('post_category_id')
                    ->label('Chuyên mục')
                    ->relationship('category', 'name'),
                TernaryFilter::make('published')
                    ->label('Trạng thái')
                    ->trueLabel('Đã đăng')
                    ->falseLabel('Nháp / hẹn giờ')
                    ->queries(
                        true: fn (Builder $query) => $query->published(),
                        false: fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '>', now())),
                    ),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Xem')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Post $record): string => route('posts.show', $record), shouldOpenInNewTab: true)
                    ->visible(fn (Post $record): bool => $record->isPublished()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
