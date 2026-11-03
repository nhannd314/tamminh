<?php

namespace App\Filament\Resources\PalmReadingBookings;

use App\Filament\Resources\PalmReadingBookings\Pages\ManagePalmReadingBookings;
use App\Models\PalmReadingBooking;
use App\PalmReadingPackage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Danh sách lịch xem chỉ tay khách gửi từ trang chủ; admin gọi lại rồi đánh dấu "Đã liên hệ".
 */
class PalmReadingBookingResource extends Resource
{
    protected static ?string $model = PalmReadingBooking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'lịch xem chỉ tay';

    protected static ?string $pluralModelLabel = 'Lịch xem chỉ tay';

    protected static ?string $slug = 'lich-xem-chi-tay';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Số lịch chưa liên hệ hiện trên menu.
     */
    public static function getNavigationBadge(): ?string
    {
        $pendingCount = static::getModel()::query()->whereNull('contacted_at')->count();

        return $pendingCount > 0 ? (string) $pendingCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->searchable()
                    ->copyable()
                    ->url(fn (PalmReadingBooking $record): string => 'tel:'.$record->phone),
                TextColumn::make('gender')
                    ->label('Giới tính')
                    ->formatStateUsing(fn (PalmReadingBooking $record): string => $record->gender->label()),
                TextColumn::make('birth_date')
                    ->label('Ngày sinh')
                    ->date('d/m/Y'),
                TextColumn::make('package')
                    ->label('Gói xem')
                    ->badge()
                    ->formatStateUsing(fn (PalmReadingBooking $record): string => $record->package->label())
                    ->color(fn (PalmReadingBooking $record): string => $record->package === PalmReadingPackage::Premium ? 'warning' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Thời gian đặt')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                TextColumn::make('contacted_at')
                    ->label('Đã liên hệ')
                    ->dateTime('H:i d/m/Y')
                    ->placeholder('Chưa liên hệ')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('contacted')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã liên hệ')
                    ->falseLabel('Chưa liên hệ')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('contacted_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('contacted_at'),
                    ),
                SelectFilter::make('package')
                    ->label('Gói xem')
                    ->options(collect(PalmReadingPackage::cases())->mapWithKeys(fn (PalmReadingPackage $package): array => [$package->value => $package->label()])),
            ])
            ->recordActions([
                Action::make('markContacted')
                    ->label('Đã liên hệ')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (PalmReadingBooking $record): bool => ! $record->isContacted())
                    ->action(fn (PalmReadingBooking $record) => $record->update(['contacted_at' => now()])),
                Action::make('markPending')
                    ->label('Chưa liên hệ')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('gray')
                    ->visible(fn (PalmReadingBooking $record): bool => $record->isContacted())
                    ->action(fn (PalmReadingBooking $record) => $record->update(['contacted_at' => null])),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePalmReadingBookings::route('/'),
        ];
    }
}
