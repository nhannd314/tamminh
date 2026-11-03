<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
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
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Cửa hàng';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'sản phẩm';

    protected static ?string $pluralModelLabel = 'Sản phẩm';

    protected static ?string $slug = 'san-pham';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(ProductForm::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên sản phẩm')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Nhóm')
                    ->color('gray'),
                TextColumn::make('price')
                    ->label('Giá')
                    ->numeric(thousandsSeparator: '.')
                    ->suffix('đ')
                    ->alignEnd(),
                TextColumn::make('stock')
                    ->label('Tồn kho')
                    ->alignEnd(),
                IconColumn::make('is_active')
                    ->label('Đang bán')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Nhóm')
                    ->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
