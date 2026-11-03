<?php

namespace App\Filament\Resources\Experts;

use App\Filament\Resources\Experts\Pages\ManageExperts;
use App\Filament\Resources\Experts\Schemas\ExpertForm;
use App\Models\Expert;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ExpertResource extends Resource
{
    protected static ?string $model = Expert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Nội dung';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'chuyên gia';

    protected static ?string $pluralModelLabel = 'Chuyên gia';

    protected static ?string $slug = 'chuyen-gia';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(ExpertForm::components());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')
                    ->label('Tên chuyên gia')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Điện thoại')
                    ->color('gray'),
                TextColumn::make('years_experience')
                    ->label('Kinh nghiệm')
                    ->suffix(' năm')
                    ->alignEnd(),
                IconColumn::make('is_active')
                    ->label('Hiển thị')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageExperts::route('/'),
        ];
    }
}
