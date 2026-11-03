<?php

namespace App\Filament\Resources\AffiliateCommissions;

use App\AffiliateCommissionStatus;
use App\Filament\Resources\AffiliateCommissions\Pages\ManageAffiliateCommissions;
use App\Models\AffiliateCommission;
use App\Models\AnalysisOrder;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Từng khoản hoa hồng affiliate (tự sinh từ đơn); admin chỉ xem, lọc và huỷ khoản gian lận.
 */
class AffiliateCommissionResource extends Resource
{
    protected static ?string $model = AffiliateCommission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'hoa hồng';

    protected static ?string $pluralModelLabel = 'Hoa hồng';

    protected static ?string $slug = 'hoa-hong';

    public static function table(Table $table): Table
    {
        $money = fn (?int $amount): string => number_format((int) $amount, 0, ',', '.').'đ';

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['affiliate.user', 'commissionable']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
                TextColumn::make('affiliate.user.name')
                    ->label('Cộng tác viên')
                    ->description(fn (AffiliateCommission $record): string => $record->affiliate->code)
                    ->searchable(),
                TextColumn::make('commissionable_type')
                    ->label('Đơn')
                    ->formatStateUsing(fn (AffiliateCommission $record): string => $record->sourceLabel())
                    ->description(fn (AffiliateCommission $record): ?string => match (true) {
                        $record->commissionable instanceof Order => $record->commissionable->customer_name.' · '.$record->commissionable->status->label(),
                        $record->commissionable instanceof AnalysisOrder => $record->commissionable->faceAnalysis?->name,
                        default => null,
                    }),
                TextColumn::make('base_amount')
                    ->label('Giá trị đơn')
                    ->formatStateUsing(fn (int $state): string => $money($state))
                    ->alignEnd(),
                TextColumn::make('amount')
                    ->label('Hoa hồng')
                    ->formatStateUsing(fn (int $state): string => $money($state))
                    ->description(fn (AffiliateCommission $record): string => $record->rate_percent.'%')
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (AffiliateCommissionStatus $state): string => $state->label())
                    ->color(fn (AffiliateCommissionStatus $state): string => $state->color())
                    ->description(fn (AffiliateCommission $record): ?string => $record->paid_at ? 'Chi trả '.$record->paid_at->format('d/m/Y').($record->payout_note ? ' · '.$record->payout_note : '') : null),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(collect(AffiliateCommissionStatus::cases())->mapWithKeys(fn (AffiliateCommissionStatus $status): array => [$status->value => $status->label()])),
                SelectFilter::make('commissionable_type')
                    ->label('Loại đơn')
                    ->options(['order' => 'Vật phẩm', 'analysis_order' => 'Xem tướng']),
                SelectFilter::make('affiliate')
                    ->label('Cộng tác viên')
                    ->relationship('affiliate', 'code')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label('Huỷ hoa hồng')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Dùng khi đơn gian lận hoặc bị hoàn trả. Không thể hoàn tác.')
                    ->visible(fn (AffiliateCommission $record): bool => $record->isCancellable())
                    ->action(fn (AffiliateCommission $record) => $record->cancel()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAffiliateCommissions::route('/'),
        ];
    }
}
