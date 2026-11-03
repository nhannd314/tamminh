<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Models\User;
use App\UserRole;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * Quản lý tài khoản khách và quản trị viên. `role` không mass-assign được nên được gán riêng khi lưu.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'người dùng';

    protected static ?string $pluralModelLabel = 'Người dùng';

    protected static ?string $slug = 'nguoi-dung';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(UserForm::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount(['orders', 'palmReadingBookings']))
            ->columns([
                TextColumn::make('name')
                    ->label('Họ tên')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(['name', 'email', 'phone']),
                TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->placeholder('—')
                    ->color('gray'),
                TextColumn::make('role')
                    ->label('Vai trò')
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->color(fn (UserRole $state): string => $state === UserRole::Admin ? 'danger' : 'gray'),
                TextColumn::make('orders_count')
                    ->label('Đơn hàng')
                    ->alignEnd(),
                TextColumn::make('palm_reading_bookings_count')
                    ->label('Lịch xem chỉ tay')
                    ->alignEnd(),
                IconColumn::make('google_id')
                    ->label('Google')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => filled($record->google_id)),
                TextColumn::make('created_at')
                    ->label('Đăng ký')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options(collect(UserRole::cases())->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])->all()),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->using(fn (User $record, array $data): User => static::saveUser($record, $data)),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => ! $record->is(auth()->user())),
            ]);
    }

    /**
     * Lưu thông tin người dùng, gán `role` riêng vì không nằm trong Fillable.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveUser(User $user, array $data): User
    {
        $role = $data['role'] ?? null;
        unset($data['role']);

        $user->fill($data);

        if ($role !== null && ! $user->is(auth()->user())) {
            $user->role = UserRole::from($role);
        }

        $user->save();

        return $user;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
