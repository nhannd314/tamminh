<?php

namespace App\Filament\Resources\Affiliates;

use App\AffiliateCommissionStatus;
use App\Filament\Resources\Affiliates\Pages\ManageAffiliates;
use App\Models\Affiliate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Cộng tác viên affiliate: số dư hoa hồng, tài khoản nhận tiền, ghi nhận đã chi trả và khoá tài khoản gian lận.
 */
class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'cộng tác viên';

    protected static ?string $pluralModelLabel = 'Cộng tác viên';

    protected static ?string $slug = 'cong-tac-vien';

    protected static ?string $recordTitleAttribute = 'code';

    /**
     * Số cộng tác viên đang có hoa hồng chờ chi trả.
     */
    public static function getNavigationBadge(): ?string
    {
        $awaitingPayoutCount = static::getModel()::query()
            ->whereHas('commissions', fn (Builder $query): Builder => $query->where('status', AffiliateCommissionStatus::Approved))
            ->count();

        return $awaitingPayoutCount > 0 ? (string) $awaitingPayoutCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function table(Table $table): Table
    {
        $money = fn (?int $amount): string => number_format((int) $amount, 0, ',', '.').'đ';
        $sumOf = fn (AffiliateCommissionStatus $status): array => [
            'commissions as '.$status->value.'_total' => fn (Builder $query): Builder => $query->where('status', $status),
        ];

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with('user')
                ->withSum($sumOf(AffiliateCommissionStatus::Pending), 'amount')
                ->withSum($sumOf(AffiliateCommissionStatus::Approved), 'amount')
                ->withSum($sumOf(AffiliateCommissionStatus::Paid), 'amount'))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Cộng tác viên')
                    ->description(fn (Affiliate $record): string => $record->user->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('code')
                    ->label('Mã')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('pending_total')
                    ->label('Chờ xác nhận')
                    ->formatStateUsing(fn ($state): string => $money((int) $state))
                    ->default(0)
                    ->alignEnd(),
                TextColumn::make('approved_total')
                    ->label('Chờ chi trả')
                    ->formatStateUsing(fn ($state): string => $money((int) $state))
                    ->default(0)
                    ->weight('bold')
                    ->color(fn ($state): string => (int) $state > 0 ? 'info' : 'gray')
                    ->alignEnd(),
                TextColumn::make('paid_total')
                    ->label('Đã chi trả')
                    ->formatStateUsing(fn ($state): string => $money((int) $state))
                    ->default(0)
                    ->alignEnd(),
                TextColumn::make('bank_account_number')
                    ->label('Tài khoản nhận')
                    ->formatStateUsing(fn (Affiliate $record): string => $record->bank_account_number)
                    ->description(fn (Affiliate $record): ?string => $record->hasBankAccount() ? $record->bank_name.' · '.$record->bank_account_name : null)
                    ->placeholder('Chưa khai báo')
                    ->copyable(),
                TextColumn::make('suspended_at')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (): string => 'Đã khoá')
                    ->placeholder('Hoạt động')
                    ->color('danger'),
                TextColumn::make('created_at')
                    ->label('Tham gia')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('awaiting_payout')
                    ->label('Chờ chi trả')
                    ->placeholder('Tất cả')
                    ->trueLabel('Có hoa hồng chờ chi trả')
                    ->falseLabel('Không có')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('commissions', fn (Builder $commissions): Builder => $commissions->where('status', AffiliateCommissionStatus::Approved)),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('commissions', fn (Builder $commissions): Builder => $commissions->where('status', AffiliateCommissionStatus::Approved)),
                    ),
                TernaryFilter::make('suspended')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã khoá')
                    ->falseLabel('Hoạt động')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('suspended_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('suspended_at'),
                    ),
            ])
            ->recordActions([
                Action::make('payOut')
                    ->label('Chi trả')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->visible(fn (Affiliate $record): bool => (int) $record->approved_total > 0)
                    ->modalHeading(fn (Affiliate $record): string => 'Chi trả hoa hồng cho '.$record->user->name)
                    ->modalDescription('Chuyển khoản số tiền dưới đây vào tài khoản của cộng tác viên trước, rồi bấm xác nhận để ghi nhận đã chi trả.')
                    ->schema(fn (Affiliate $record): array => [
                        TextEntry::make('amount')->label('Số tiền')->state($money((int) $record->approved_total))->weight('bold'),
                        TextEntry::make('bank')->label('Tài khoản nhận')
                            ->state($record->hasBankAccount() ? "{$record->bank_name} · {$record->bank_account_number} · {$record->bank_account_name}" : 'Cộng tác viên chưa khai báo tài khoản ngân hàng'),
                        TextInput::make('note')->label('Ghi chú (mã giao dịch…)')->maxLength(255),
                    ])
                    ->modalSubmitActionLabel('Đã chuyển khoản')
                    ->action(function (Affiliate $record, array $data): void {
                        $paidAmount = $record->payOut($data['note'] ?? null);

                        Notification::make()->success()->title('Đã ghi nhận chi trả '.number_format($paidAmount, 0, ',', '.').'đ')->send();
                    }),
                ActionGroup::make([
                    Action::make('suspend')
                        ->label('Khoá affiliate')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Link giới thiệu sẽ không ghi nhận đơn mới. Hoa hồng đã có vẫn giữ nguyên, bạn có thể huỷ từng khoản ở mục Hoa hồng.')
                        ->visible(fn (Affiliate $record): bool => $record->isActive())
                        ->action(fn (Affiliate $record) => $record->update(['suspended_at' => now()])),
                    Action::make('unsuspend')
                        ->label('Mở khoá')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->visible(fn (Affiliate $record): bool => ! $record->isActive())
                        ->action(fn (Affiliate $record) => $record->update(['suspended_at' => null])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAffiliates::route('/'),
        ];
    }
}
