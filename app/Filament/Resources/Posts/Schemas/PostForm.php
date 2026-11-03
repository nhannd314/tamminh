<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Resources\PostCategories\Schemas\PostCategoryForm;
use App\Models\Post;
use App\PostTone;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
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
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Post $record): void {
                                // Chỉ tự điền slug khi tạo mới, để không đổi link của bài đã đăng.
                                if ($record === null && blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->prefix('/bai-viet/')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        Textarea::make('excerpt')
                            ->label('Tóm tắt')
                            ->helperText('Hiện trên thẻ bài viết và dưới tiêu đề.')
                            ->required()
                            ->rows(3)
                            ->maxLength(500),
                        RichEditor::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->fileAttachmentsDirectory('posts')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'horizontalRule'],
                                ['table', 'attachFiles'],
                                ['undo', 'redo'],
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Đăng bài')
                            ->schema([
                                Select::make('post_category_id')
                                    ->label('Chuyên mục')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->preload()
                                    ->createOptionForm(PostCategoryForm::components()),
                                TextInput::make('author_name')
                                    ->label('Tác giả')
                                    ->required()
                                    ->maxLength(255)
                                    ->default('Thầy Minh Quang'),
                                DateTimePicker::make('published_at')
                                    ->label('Ngày đăng')
                                    ->helperText('Để trống = bản nháp. Chọn ngày tương lai để hẹn giờ đăng.')
                                    ->seconds(false),
                            ]),
                        Section::make('Ảnh bìa')
                            ->schema([
                                FileUpload::make('cover_path')
                                    ->label('Ảnh bìa')
                                    ->helperText('Không bắt buộc. Không có ảnh thì dùng chữ Hán trên nền màu bên dưới.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('posts/covers')
                                    ->maxSize(4096),
                                TextInput::make('glyph')
                                    ->label('Chữ Hán trên ảnh bìa')
                                    ->required()
                                    ->maxLength(2)
                                    ->default('相'),
                                Select::make('tone')
                                    ->label('Tông màu')
                                    ->options(PostTone::class)
                                    ->required()
                                    ->default(PostTone::Copper),
                            ]),
                        Section::make('SEO')
                            ->collapsed()
                            ->schema([
                                Textarea::make('meta_description')
                                    ->label('Mô tả SEO')
                                    ->helperText('Để trống thì dùng phần tóm tắt.')
                                    ->rows(3)
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }
}
