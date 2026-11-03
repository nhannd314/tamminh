<?php

namespace App\Filament\Pages;

use App\AnalysisSection;
use App\Models\Affiliate;
use App\Models\Setting;
use App\OrderPaymentMethod;
use App\Support\ShopContact;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Illuminate\Support\Str;

/**
 * Trang cấu hình hệ thống: đọc/ghi bảng `settings` (bảng giá, cảm nhận khách hàng, hỏi đáp…). Cấu hình SePay nằm ở .env.
 * Mỗi trường form ánh xạ tới một khoá setting qua fields(); khoá chưa lưu lấy mặc định từ defaults() (config/site.php).
 *
 * @property-read Schema $form
 */
class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Cấu hình';

    protected static ?string $title = 'Cấu hình hệ thống';

    protected static ?string $slug = 'cau-hinh';

    protected static ?int $navigationSort = 100;

    /**
     * Kênh liên hệ quản lý ở tab Liên hệ, ứng với config('site.*') và ShopContact.
     *
     * @var list<string>
     */
    private const CONTACT_CHANNELS = ['phone', 'zalo', 'email', 'address', 'hours', ...ShopContact::SOCIAL_NETWORKS];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $defaults = $this->defaults();

        $this->form->fill(collect($this->fields())->map(fn (string $key, string $field) => Setting::get($key, $defaults[$field] ?? null))->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        $this->pricingTab(),
                        $this->shopTab(),
                        $this->affiliateTab(),
                        $this->contactTab(),
                        $this->testimonialsTab(),
                        $this->faqsTab(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Lưu cấu hình')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($this->fields() as $field => $key) {
            Setting::set($key, $this->normalize($field, $state[$field] ?? null));
        }

        Notification::make()->success()->title('Đã lưu cấu hình')->send();
    }

    /**
     * Trường form → khoá trong bảng settings.
     *
     * @return array<string, string>
     */
    private function fields(): array
    {
        $prices = collect(AnalysisSection::cases())
            ->reject(fn (AnalysisSection $section): bool => $section === AnalysisSection::Overview)
            ->mapWithKeys(fn (AnalysisSection $section): array => ['price_'.$section->value => 'pricing.section.'.$section->value]);

        return [
            ...$prices,
            'full_package_discount_percent' => 'pricing.full_package_discount_percent',
            'shipping_fee' => 'shop.shipping_fee',
            'affiliate_product_percent' => 'affiliate.product_commission_percent',
            'affiliate_analysis_percent' => 'affiliate.analysis_commission_percent',
            'affiliate_cookie_days' => 'affiliate.cookie_days',
            ...collect(self::CONTACT_CHANNELS)->mapWithKeys(fn (string $channel): array => ['contact_'.$channel => ShopContact::settingKey($channel)])->all(),
            'testimonials' => 'home.testimonials',
            'faqs' => 'home.faqs',
        ];
    }

    /**
     * Giá trị mặc định khi khoá chưa có trong bảng settings.
     *
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'shipping_fee' => OrderPaymentMethod::DEFAULT_SHIPPING_FEE,
            'affiliate_product_percent' => Affiliate::DEFAULT_COMMISSION_PERCENT,
            'affiliate_analysis_percent' => Affiliate::DEFAULT_COMMISSION_PERCENT,
            'affiliate_cookie_days' => Affiliate::DEFAULT_COOKIE_DAYS,
            ...collect(self::CONTACT_CHANNELS)->mapWithKeys(fn (string $channel): array => ['contact_'.$channel => config('site.'.$channel)])->all(),
            'testimonials' => config('site.testimonials', []),
            'faqs' => config('site.faqs', []),
        ];
    }

    private function normalize(string $field, mixed $value): mixed
    {
        return match (true) {
            Str::startsWith($field, 'price_'), Str::startsWith($field, 'affiliate_'), $field === 'full_package_discount_percent', $field === 'shipping_fee' => (int) $value,
            $field === 'testimonials' => collect($value ?? [])->map(fn (array $testimonial): array => [
                ...array_map(fn (mixed $part): mixed => is_string($part) ? trim($part) : $part, $testimonial),
                'rating' => (int) $testimonial['rating'],
            ])->values()->all(),
            $field === 'faqs' => collect($value ?? [])->map(fn (array $faq): array => array_map(trim(...), $faq))->values()->all(),
            default => trim((string) $value),
        };
    }

    private function pricingTab(): Tab
    {
        $paidSections = collect(AnalysisSection::cases())->reject(fn (AnalysisSection $section): bool => $section === AnalysisSection::Overview);

        return Tab::make('Bảng giá')
            ->icon(Heroicon::OutlinedBanknotes)
            ->schema([
                Section::make('Giá lẻ từng phần')
                    ->description('Giá mở khóa một phần luận giải. Để 0 thì phần đó miễn phí và sinh nội dung ngay khi gửi ảnh. Tổng quan luôn miễn phí.')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'xl' => 3])->schema(
                            $paidSections->map(fn (AnalysisSection $section): TextInput => $this->moneyInput('price_'.$section->value)
                                ->label('Phần '.$section->number().' · '.$section->label()))->all(),
                        ),
                    ]),

                Section::make('Trọn gói')
                    ->description('Mở khóa tất cả các phần có phí trong một lần thanh toán.')
                    ->schema([
                        TextInput::make('full_package_discount_percent')
                            ->label('Chiết khấu trọn gói')
                            ->numeric()->integer()->minValue(0)->maxValue(100)->required()
                            ->suffix('%')
                            ->helperText('Giá trọn gói = tổng giá lẻ các phần có phí × (100% - chiết khấu). Đã mua lẻ phần nào thì được trừ giá lẻ phần đó × (100% - chiết khấu). VD: 6 phần × 20.000đ, chiết khấu 20% → trọn gói 96.000đ; đã mua 1 phần → còn 80.000đ.'),
                    ]),
            ]);
    }

    private function shopTab(): Tab
    {
        return Tab::make('Cửa hàng')
            ->icon(Heroicon::OutlinedShoppingBag)
            ->schema([
                Section::make('Giao hàng')
                    ->description('Thanh toán chuyển khoản online luôn được miễn phí giao hàng.')
                    ->schema([
                        $this->moneyInput('shipping_fee')
                            ->label('Phí giao hàng khi thanh toán COD')
                            ->helperText('Để 0 nếu miễn phí giao hàng cho mọi đơn.'),
                    ]),
            ]);
    }

    private function affiliateTab(): Tab
    {
        $percentInput = fn (string $name): TextInput => TextInput::make($name)
            ->numeric()->integer()->minValue(0)->maxValue(100)->required()
            ->suffix('%');

        return Tab::make('Affiliate')
            ->icon(Heroicon::OutlinedUserGroup)
            ->schema([
                Section::make('Hoa hồng')
                    ->description('Tỉ lệ được chốt tại lúc ghi nhận đơn: đổi tỉ lệ không ảnh hưởng hoa hồng đã có. Để 0 để tạm ngưng hoa hồng loại đó.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            $percentInput('affiliate_product_percent')
                                ->label('Vật phẩm phong thủy')
                                ->helperText('Tính trên tiền hàng, không gồm phí giao. Xác nhận khi đơn Hoàn tất.'),
                            $percentInput('affiliate_analysis_percent')
                                ->label('Xem tướng online')
                                ->helperText('Tính trên số tiền mở khoá luận giải. Xác nhận ngay khi thanh toán.'),
                            TextInput::make('affiliate_cookie_days')
                                ->label('Hiệu lực link giới thiệu')
                                ->numeric()->integer()->minValue(1)->maxValue(365)->required()
                                ->suffix('ngày'),
                        ]),
                    ]),
            ]);
    }

    private function contactTab(): Tab
    {
        return Tab::make('Liên hệ')
            ->icon(Heroicon::OutlinedPhone)
            ->schema([
                Section::make('Kênh liên hệ')
                    ->description('Hiển thị ở footer, trang Liên hệ, giỏ hàng, thanh toán và cửa sổ thanh toán luận giải.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextInput::make('contact_phone')
                                ->label('Hotline')
                                ->tel()
                                ->required()
                                ->maxLength(32),
                            TextInput::make('contact_zalo')
                                ->label('Số Zalo')
                                ->helperText('Số điện thoại đăng ký Zalo của cửa hàng, dùng cho các nút "Nhắn Zalo".')
                                ->tel()
                                ->required()
                                ->maxLength(32),
                            TextInput::make('contact_email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            TextInput::make('contact_hours')
                                ->label('Giờ làm việc')
                                ->maxLength(255),
                        ]),
                        TextInput::make('contact_address')
                            ->label('Địa chỉ')
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make('Mạng xã hội')
                    ->description('Biểu tượng ở footer. Để trống thì ẩn biểu tượng đó. Nút Zalo dùng số Zalo ở trên.')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])->schema([
                            TextInput::make('contact_facebook')
                                ->label('Facebook')
                                ->url()
                                ->placeholder('https://facebook.com/tamminh')
                                ->maxLength(255),
                            TextInput::make('contact_youtube')
                                ->label('YouTube')
                                ->url()
                                ->placeholder('https://youtube.com/@tamminh')
                                ->maxLength(255),
                            TextInput::make('contact_tiktok')
                                ->label('TikTok')
                                ->url()
                                ->placeholder('https://tiktok.com/@tamminh')
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }

    private function testimonialsTab(): Tab
    {
        return Tab::make('Cảm nhận khách hàng')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->schema([
                Repeater::make('testimonials')
                    ->hiddenLabel()
                    ->addActionLabel('Thêm cảm nhận')
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Textarea::make('quote')
                            ->label('Lời cảm nhận')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->label('Tên khách hàng')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('role')
                            ->label('Nghề nghiệp · Nơi ở')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('service')
                            ->label('Dịch vụ đã dùng')
                            ->required()
                            ->maxLength(100),
                        Select::make('rating')
                            ->label('Số sao')
                            ->options([5 => '5 sao', 4 => '4 sao', 3 => '3 sao', 2 => '2 sao', 1 => '1 sao'])
                            ->default(5)
                            ->required()
                            ->selectablePlaceholder(false),
                        TextInput::make('avatar')
                            ->label('Chữ đại diện')
                            ->helperText('1-2 ký tự hiện trong ô tròn, VD: chữ cái đầu của tên.')
                            ->required()
                            ->maxLength(2),
                        Select::make('tone')
                            ->label('Màu ô đại diện')
                            ->options(['copper' => 'Đồng', 'navy' => 'Xanh đậm'])
                            ->default('copper')
                            ->required()
                            ->selectablePlaceholder(false),
                    ]),
            ]);
    }

    private function faqsTab(): Tab
    {
        return Tab::make('Hỏi đáp')
            ->icon(Heroicon::OutlinedQuestionMarkCircle)
            ->schema([
                Repeater::make('faqs')
                    ->hiddenLabel()
                    ->addActionLabel('Thêm câu hỏi')
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                    ->schema([
                        TextInput::make('question')
                            ->label('Câu hỏi')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('answer')
                            ->label('Câu trả lời')
                            ->helperText('Gõ :portrait_days để hiện số ngày lưu ảnh chân dung.')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000),
                    ]),
            ]);
    }

    private function moneyInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->required()
            ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
            ->stripCharacters('.')
            ->numeric()
            ->minValue(0)
            ->suffix('đ');
    }
}
