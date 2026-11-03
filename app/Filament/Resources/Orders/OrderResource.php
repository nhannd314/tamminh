<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Models\Order;
use App\OrderPaymentMethod;
use App\OrderStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Đơn vật phẩm đặt từ trang thanh toán: xem chi tiết, chuyển trạng thái, ghi nhận đã thu tiền, huỷ (trả lại tồn kho).
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Cửa hàng';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'đơn hàng';

    protected static ?string $pluralModelLabel = 'Đơn hàng';

    protected static ?string $slug = 'don-hang';

    protected static ?string $recordTitleAttribute = 'code';

    /**
     * Số đơn mới chưa xử lý hiện trên menu.
     */
    public static function getNavigationBadge(): ?string
    {
        $newCount = static::getModel()::query()->where('status', OrderStatus::New)->count();

        return $newCount > 0 ? (string) $newCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        $money = fn (?int $amount): string => number_format((int) $amount, 0, ',', '.').'đ';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Người nhận')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('customer_name')->label('Họ và tên'),
                            TextEntry::make('phone')->label('Điện thoại')->copyable(),
                        ]),
                        TextEntry::make('address')->label('Địa chỉ'),
                        TextEntry::make('note')->label('Ghi chú')->placeholder('Không có'),
                        TextEntry::make('affiliate.code')->label('Affiliate giới thiệu')
                            ->formatStateUsing(fn (Order $record): string => $record->affiliate->code.' · '.$record->affiliate->user->name)
                            ->placeholder('Không có'),
                    ]),
                Section::make('Vật phẩm')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name')->label('Vật phẩm')->columnSpan(2)
                                    ->helperText(fn ($record): ?string => $record->optionsLabel() ?: null),
                                TextEntry::make('quantity')->label('SL'),
                                TextEntry::make('subtotal')->label('Thành tiền')->formatStateUsing(fn (int $state): string => $money($state)),
                            ]),
                        Grid::make(3)->schema([
                            TextEntry::make('subtotal')->label('Tạm tính')->formatStateUsing(fn (int $state): string => $money($state)),
                            TextEntry::make('shipping_fee')->label('Phí giao hàng')->formatStateUsing(fn (int $state): string => $state > 0 ? $money($state) : 'Miễn phí'),
                            TextEntry::make('total')->label('Tổng cộng')->weight('bold')->formatStateUsing(fn (int $state): string => $money($state)),
                        ]),
                    ]),
                Section::make('Thanh toán')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('payment_method')->label('Hình thức')->formatStateUsing(fn (OrderPaymentMethod $state): string => $state->label()),
                            TextEntry::make('paid_at')->label('Đã thu tiền')->dateTime('H:i d/m/Y')->placeholder('Chưa'),
                            TextEntry::make('transaction_id')->label('Mã giao dịch')->placeholder('—'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('items'))
            ->columns([
                TextColumn::make('code')
                    ->label('Mã đơn')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('customer_name')
                    ->label('Khách hàng')
                    ->description(fn (Order $record): string => $record->phone)
                    ->searchable(['customer_name', 'phone']),
                TextColumn::make('total')
                    ->label('Tổng tiền')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.').'đ')
                    ->description(fn (Order $record): string => $record->items_count.' vật phẩm')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Thanh toán')
                    ->badge()
                    ->formatStateUsing(fn (Order $record): string => $record->payment_method === OrderPaymentMethod::Online ? 'Online' : 'COD')
                    ->description(fn (Order $record): string => $record->isPaid() ? 'Đã thu tiền' : 'Chưa thu tiền')
                    ->color(fn (Order $record): string => $record->isPaid() ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Thời gian đặt')
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->label()])),
                SelectFilter::make('payment_method')
                    ->label('Hình thức thanh toán')
                    ->options(collect(OrderPaymentMethod::cases())->mapWithKeys(fn (OrderPaymentMethod $method): array => [$method->value => $method->label()])),
                TernaryFilter::make('paid')
                    ->label('Thu tiền')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đã thu tiền')
                    ->falseLabel('Chưa thu tiền')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('paid_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('paid_at'),
                    ),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                ActionGroup::make([
                    Action::make('changeStatus')
                        ->label('Đổi trạng thái')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->visible(fn (Order $record): bool => ! $record->isCancelled())
                        ->fillForm(fn (Order $record): array => ['status' => $record->status])
                        ->schema([
                            Select::make('status')
                                ->label('Trạng thái')
                                ->options(collect(OrderStatus::cases())
                                    ->reject(fn (OrderStatus $status): bool => $status === OrderStatus::Cancelled)
                                    ->mapWithKeys(fn (OrderStatus $status): array => [$status->value => $status->label()]))
                                ->required()
                                ->selectablePlaceholder(false),
                        ])
                        ->action(fn (Order $record, array $data) => $record->update(['status' => $data['status']])),
                    Action::make('markPaid')
                        ->label('Đã thu tiền')
                        ->icon(Heroicon::OutlinedBanknotes)
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Ghi nhận đơn này đã được thanh toán (VD: đã thu tiền COD hoặc đối soát chuyển khoản thủ công).')
                        ->visible(fn (Order $record): bool => ! $record->isPaid() && ! $record->isCancelled())
                        ->action(fn (Order $record) => $record->markAsPaid('manual')),
                    Action::make('cancel')
                        ->label('Huỷ đơn')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Huỷ đơn và trả số lượng vật phẩm về tồn kho. Không thể hoàn tác.')
                        ->visible(fn (Order $record): bool => ! $record->isCancelled())
                        ->action(fn (Order $record) => $record->cancel()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}
