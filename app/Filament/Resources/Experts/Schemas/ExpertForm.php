<?php

namespace App\Filament\Resources\Experts\Schemas;

use App\Models\Expert;
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

class ExpertForm
{
    /**
     * @return list<Component>
     */
    public static function components(): array
    {
        return [
            Section::make('Hồ sơ')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Tên chuyên gia')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, ?Expert $record): void {
                            // Chỉ tự điền slug khi tạo mới, để không đổi link của chuyên gia đã có.
                            if ($record === null && blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('Đường dẫn')
                        ->prefix('/chuyen-gia/')
                        ->required()
                        ->maxLength(255)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),
                    Select::make('user_id')
                        ->label('Tài khoản liên kết')
                        ->helperText('Không bắt buộc. Mỗi tài khoản chỉ gắn với một chuyên gia.')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->unique(ignoreRecord: true),
                    TextInput::make('title')
                        ->label('Chức danh')
                        ->helperText('Hiện dưới tên, ví dụ: Chuyên gia nhân tướng học · Hà Nội.')
                        ->maxLength(255),
                    TextInput::make('headline')
                        ->label('Tiêu đề giới thiệu')
                        ->helperText('Câu giới thiệu nổi bật ở đầu hồ sơ.')
                        ->maxLength(255),
                    TextInput::make('glyph')
                        ->label('Chữ Hán thay ảnh')
                        ->helperText('Hiện khi chưa có ảnh.')
                        ->required()
                        ->maxLength(2)
                        ->default('相'),
                    FileUpload::make('photo_path')
                        ->label('Ảnh đại diện')
                        ->image()
                        ->disk('public')
                        ->directory('experts')
                        ->maxSize(4096)
                        ->columnSpanFull(),
                    TextInput::make('years_experience')
                        ->label('Số năm kinh nghiệm')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('sessions_count')
                        ->label('Lượt tư vấn')
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
                    Textarea::make('description')
                        ->label('Mô tả chi tiết')
                        ->helperText('Mỗi dòng xuống hàng là một đoạn văn.')
                        ->rows(8)
                        ->columnSpanFull(),
                    TagsInput::make('specialties')
                        ->label('Chuyên môn')
                        ->columnSpanFull(),
                ]),
            Section::make('Dịch vụ & chi phí')
                ->collapsed()
                ->schema([
                    Repeater::make('services')
                        ->hiddenLabel()
                        ->addActionLabel('Thêm dịch vụ')
                        ->defaultItems(0)
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')->label('Tên dịch vụ')->required()->maxLength(255),
                            TextInput::make('price')->label('Giá')->helperText('Ví dụ: 800.000đ')->maxLength(64),
                            TextInput::make('duration')->label('Thời lượng')->helperText('Ví dụ: 60 phút, Tại nhà')->maxLength(64),
                            Textarea::make('description')->label('Mô tả')->rows(2)->maxLength(500),
                        ]),
                ]),
            Section::make('Khách hàng nói gì')
                ->collapsed()
                ->schema([
                    Repeater::make('reviews')
                        ->hiddenLabel()
                        ->addActionLabel('Thêm đánh giá')
                        ->defaultItems(0)
                        ->columns(3)
                        ->schema([
                            TextInput::make('name')->label('Khách hàng')->required()->maxLength(255),
                            TextInput::make('location')->label('Nơi ở')->maxLength(255),
                            TextInput::make('rating')->label('Số sao')->numeric()->minValue(1)->maxValue(5)->default(5)->required(),
                            Textarea::make('quote')->label('Nội dung')->required()->rows(3)->maxLength(1000)->columnSpanFull(),
                        ]),
                ]),
            Section::make('Liên lạc')
                ->columns(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Điện thoại / Zalo')
                        ->helperText('Số này cũng dùng cho nút nhắn tin Zalo.')
                        ->tel()
                        ->maxLength(32),
                    TextInput::make('address')
                        ->label('Địa chỉ văn phòng')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('working_hours')
                        ->label('Giờ tiếp khách')
                        ->helperText('Ví dụ: Thứ 2 – Thứ 7 · 8:00 – 17:30')
                        ->maxLength(255),
                    FileUpload::make('office_image_path')
                        ->label('Ảnh văn phòng / bản đồ')
                        ->image()
                        ->disk('public')
                        ->directory('experts/offices')
                        ->maxSize(4096),
                    TextInput::make('tiktok_url')
                        ->label('Kênh TikTok')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('facebook_url')
                        ->label('Facebook')
                        ->url()
                        ->maxLength(255),
                    TextInput::make('youtube_url')
                        ->label('Kênh YouTube')
                        ->url()
                        ->maxLength(255),
                ]),
            Section::make('Hiển thị')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Hiển thị ngoài trang chủ')
                        ->default(true),
                    Toggle::make('is_accepting_bookings')
                        ->label('Đang nhận lịch')
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
