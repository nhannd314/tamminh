<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\ManageProductCategories;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Cửa hàng';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'nhóm sản phẩm';

    protected static ?string $pluralModelLabel = 'Nhóm sản phẩm';

    protected static ?string $slug = 'nhom-vat-pham';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Tên nhóm')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?ProductCategory $record): void {
                        if ($record === null && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('Đường dẫn')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(ignoreRecord: true),
                TextInput::make('sort')
                    ->label('Thứ tự')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên nhóm')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Đường dẫn')
                    ->color('gray'),
                TextColumn::make('products_count')
                    ->label('Số sản phẩm')
                    ->counts('products')
                    ->alignEnd(),
            ])
            ->recordActions([
                EditAction::make(),
                // Bảng products ràng buộc restrictOnDelete: chỉ xoá được nhóm chưa có sản phẩm.
                DeleteAction::make()
                    ->hidden(fn (ProductCategory $record): bool => $record->products()->exists()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductCategories::route('/'),
        ];
    }
}
