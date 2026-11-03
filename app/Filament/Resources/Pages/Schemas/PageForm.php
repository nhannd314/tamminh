<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    /**
     * Đường dẫn một cấp đã dùng cho route khác, không đặt làm slug trang tĩnh.
     */
    private const RESERVED_SLUGS = ['admin', 'up', 'bai-viet', 'design-system', 'ket-qua', 'xem-tuong', 'webhooks', 'storage', 'livewire'];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Nội dung')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Page $record): void {
                                if ($record === null && blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->prefix('/')
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->notIn(self::RESERVED_SLUGS)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
                                'not_in' => 'Đường dẫn này đã được hệ thống dùng, vui lòng chọn đường dẫn khác.',
                            ]),
                        Textarea::make('summary')
                            ->label('Mô tả ngắn')
                            ->helperText('Hiện dưới tiêu đề trang.')
                            ->rows(2)
                            ->maxLength(255),
                        RichEditor::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->fileAttachmentsDirectory('pages')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'horizontalRule'],
                                ['table', 'attachFiles'],
                                ['undo', 'redo'],
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Hiển thị')
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Đang hiển thị')
                                    ->default(true),
                            ]),
                        Section::make('SEO')
                            ->schema([
                                Textarea::make('meta_description')
                                    ->label('Mô tả SEO')
                                    ->helperText('Để trống thì dùng mô tả ngắn.')
                                    ->rows(3)
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }
}
