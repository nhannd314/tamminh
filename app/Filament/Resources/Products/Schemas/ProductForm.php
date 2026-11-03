<?php

namespace App\Filament\Resources\Products\Schemas;

use App\FiveElement;
use App\Models\Product;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class ProductForm
{
    /**
     * @return list<Component>
     */
    public static function components(): array
    {
        return [
            Section::make('Thông tin')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Tên sản phẩm')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Product $record): void {
                            // Chỉ tự điền slug khi tạo mới, để không đổi link của sản phẩm đã bán.
                            if ($record === null && blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('Đường dẫn')
                        ->prefix('/san-pham/')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),
                    Select::make('product_category_id')
                        ->label('Nhóm')
                        ->relationship('category', 'name')
                        ->required()
                        ->preload(),
                    TextInput::make('sku')
                        ->label('Mã sản phẩm')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('summary')
                        ->label('Công dụng ngắn')
                        ->helperText('Hiện trên thẻ sản phẩm, ví dụ: Chiêu tài, giữ lộc.')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->label('Giới thiệu')
                        ->helperText('Đoạn mô tả dưới giá trên trang chi tiết.')
                        ->rows(3)
                        ->columnSpanFull(),
                    CheckboxList::make('elements')
                        ->label('Hợp bản mệnh')
                        ->options(FiveElement::class)
                        ->required()
                        ->columns(5)
                        ->columnSpanFull(),
                    TextInput::make('glyph')
                        ->label('Chữ Hán thay ảnh')
                        ->helperText('Hiện khi chưa có ảnh.')
                        ->required()
                        ->maxLength(2)
                        ->default('福'),
                    TextInput::make('badge')
                        ->label('Nhãn')
                        ->helperText('Ví dụ: Bán chạy, Mới.')
                        ->maxLength(32),
                    FileUpload::make('images')
                        ->label('Ảnh')
                        ->helperText('Ảnh đầu tiên là ảnh đại diện. Kéo để sắp xếp.')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->maxFiles(8)
                        ->disk('public')
                        ->directory('products')
                        ->maxSize(4096)
                        ->columnSpanFull(),
                ]),
            Section::make('Giá & kho')
                ->columns(2)
                ->schema([
                    TextInput::make('price')
                        ->label('Giá bán (đ)')
                        ->helperText('Giá mặc định, dùng để sắp xếp. Nếu có kích thước thì giá theo kích thước.')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    TextInput::make('compare_price')
                        ->label('Giá gốc (đ)')
                        ->helperText('Hiện gạch ngang bên cạnh giá bán.')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('stock')
                        ->label('Tồn kho')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('sold_count')
                        ->label('Đã bán')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('rating')
                        ->label('Điểm đánh giá')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(5)
                        ->step(0.1),
                    TextInput::make('reviews_count')
                        ->label('Số lượt đánh giá')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                ]),
            Section::make('Tùy chọn mua hàng')
                ->collapsed()
                ->schema([
                    Repeater::make('sizes')
                        ->label('Kích thước')
                        ->addActionLabel('Thêm kích thước')
                        ->defaultItems(0)
                        ->columns(3)
                        ->schema([
                            TextInput::make('name')->label('Tên')->helperText('Ví dụ: Nhỏ · 3cm')->required()->maxLength(64),
                            TextInput::make('price')->label('Giá (đ)')->numeric()->minValue(0)->required(),
                            TextInput::make('compare_price')->label('Giá gốc (đ)')->numeric()->minValue(0),
                        ]),
                    TagsInput::make('styles')
                        ->label('Kiểu dáng'),
                    Toggle::make('allows_engraving')
                        ->label('Cho phép khắc tên'),
                ]),
            Section::make('Nội dung chi tiết')
                ->collapsed()
                ->schema([
                    Textarea::make('meaning')
                        ->label('Ý nghĩa')
                        ->helperText('Mỗi dòng xuống hàng là một đoạn văn.')
                        ->rows(6),
                    Repeater::make('usage')
                        ->label('Cách đặt & đeo')
                        ->addActionLabel('Thêm ý')
                        ->defaultItems(0)
                        ->simple(TextInput::make('item')->required()->maxLength(500)),
                    Repeater::make('specs')
                        ->label('Thông số')
                        ->addActionLabel('Thêm thông số')
                        ->defaultItems(0)
                        ->columns(2)
                        ->schema([
                            TextInput::make('label')->label('Tên')->required()->maxLength(64),
                            TextInput::make('value')->label('Giá trị')->required()->maxLength(255),
                        ]),
                    Repeater::make('reviews')
                        ->label('Đánh giá nổi bật')
                        ->addActionLabel('Thêm đánh giá')
                        ->defaultItems(0)
                        ->columns(3)
                        ->schema([
                            TextInput::make('name')->label('Khách hàng')->required()->maxLength(255),
                            TextInput::make('note')->label('Ghi chú')->helperText('Ví dụ: Mệnh Kim')->maxLength(255),
                            TextInput::make('rating')->label('Số sao')->numeric()->minValue(1)->maxValue(5)->default(5)->required(),
                            Textarea::make('quote')->label('Nội dung')->required()->rows(3)->maxLength(1000)->columnSpanFull(),
                        ]),
                ]),
            Section::make('Hiển thị')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Đang bán')
                        ->default(true),
                    TextInput::make('sort')
                        ->label('Thứ tự')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ]),
        ];
    }
}
