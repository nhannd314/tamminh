<?php

namespace App\Filament\Pages;

use App\AnalysisSection;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
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
 * Trang cấu hình hệ thống: đọc/ghi bảng `settings` (bảng giá, thanh toán SePay…).
 * Mỗi trường form ánh xạ tới một khoá setting qua FIELDS.
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
     * Ngân hàng phổ biến theo mã của SePay (gợi ý, vẫn gõ được mã khác).
     */
    private const BANKS = [
        'MBBank', 'Vietcombank', 'VietinBank', 'BIDV', 'Agribank', 'Techcombank', 'ACB', 'VPBank',
        'TPBank', 'Sacombank', 'HDBank', 'VIB', 'SHB', 'MSB', 'OCB', 'SeABank', 'LPBank', 'Eximbank',
        'NamABank', 'KienLongBank', 'BacABank', 'PVcomBank', 'VietCapitalBank', 'ABBANK', 'NCB', 'CAKE', 'Timo',
    ];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect($this->fields())->map(fn (string $key) => Setting::get($key))->all());
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
                        $this->paymentTab(),
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
            'full_package' => 'pricing.full_package',
            'upgrade_credit_percent' => 'pricing.upgrade_credit_percent',
            'free_sections' => 'analysis.free_sections',
            'bank' => 'payment.bank',
            'account_number' => 'payment.account_number',
            'account_name' => 'payment.account_name',
            'transfer_content' => 'payment.transfer_content',
            'note' => 'payment.note',
        ];
    }

    private function normalize(string $field, mixed $value): mixed
    {
        return match (true) {
            Str::startsWith($field, 'price_'), $field === 'full_package', $field === 'upgrade_credit_percent' => (int) $value,
            $field === 'free_sections' => array_values((array) $value),
            $field === 'account_name' => Str::upper(Str::ascii((string) $value)),
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
                    ->description('Giá mở khóa một phần luận giải. Phần nằm trong danh sách miễn phí sẽ không tính tiền.')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'xl' => 3])->schema(
                            $paidSections->map(fn (AnalysisSection $section): TextInput => $this->moneyInput('price_'.$section->value)
                                ->label('Phần '.$section->number().' · '.$section->label()))->all(),
                        ),
                    ]),

                Section::make('Trọn gói')
                    ->description('Mở khóa tất cả các phần còn khóa trong một lần thanh toán.')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        $this->moneyInput('full_package')->label('Giá trọn gói'),
                        TextInput::make('upgrade_credit_percent')
                            ->label('Khấu trừ khi đã mua lẻ')
                            ->numeric()->integer()->minValue(0)->maxValue(100)->required()
                            ->suffix('%')
                            ->helperText('Đã mua lẻ thì giá trọn gói được trừ % này của số tiền đã trả. VD: đã mua 2 phần × 49.000đ, 50% → giảm 49.000đ. Giá trọn gói không bao giờ vượt tổng giá lẻ các phần còn lại.'),
                    ]),

                Section::make('Phần miễn phí')
                    ->description('Tổng quan luôn miễn phí. Các phần được chọn dưới đây cũng được xem miễn phí và sinh nội dung ngay khi gửi ảnh.')
                    ->schema([
                        CheckboxList::make('free_sections')
                            ->hiddenLabel()
                            ->options($paidSections->mapWithKeys(fn (AnalysisSection $section): array => [$section->value => 'Phần '.$section->number().' · '.$section->label()])->all())
                            ->columns(['default' => 1, 'sm' => 2, 'xl' => 3])
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    private function paymentTab(): Tab
    {
        $hasWebhookKey = filled(config('services.sepay.webhook_key'));

        return Tab::make('Thanh toán (SePay)')
            ->icon(Heroicon::OutlinedCreditCard)
            ->schema([
                Section::make('Tài khoản nhận tiền')
                    ->description('Hiển thị trong cửa sổ chuyển khoản và mã QR. Tài khoản phải được liên kết với SePay để tự động mở khóa.')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('bank')
                            ->label('Ngân hàng')
                            ->required()
                            ->datalist(self::BANKS)
                            ->helperText('Mã ngân hàng theo SePay, VD: MBBank, Vietcombank, ACB.'),
                        TextInput::make('account_number')
                            ->label('Số tài khoản')
                            ->required()
                            ->regex('/^[0-9]{6,20}$/')
                            ->validationMessages(['regex' => 'Số tài khoản chỉ gồm 6-20 chữ số.']),
                        TextInput::make('account_name')
                            ->label('Chủ tài khoản')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Tự động chuyển thành chữ in hoa, không dấu.'),
                        TextInput::make('transfer_content')
                            ->label('Nội dung chuyển khoản')
                            ->required()
                            ->maxLength(25)
                            ->regex('/\{code\}/')
                            ->validationMessages(['regex' => 'Nội dung phải chứa {code} (mã đơn) để SePay đối chiếu.'])
                            ->helperText('{code} là mã đơn dạng TMxxxxxxxx. Nên để nguyên {code}.'),
                        Textarea::make('note')
                            ->label('Lưu ý cho khách')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Webhook SePay')
                    ->description('Khai báo trong SePay > Webhooks để hệ thống tự mở khóa khi nhận được tiền.')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('webhook_url')
                            ->label('URL nhận webhook')
                            ->formatStateUsing(fn (): string => route('webhooks.sepay'))
                            ->readOnly()
                            ->dehydrated(false)
                            ->copyable(copyMessage: 'Đã sao chép'),
                        TextInput::make('webhook_key_status')
                            ->label('Khóa API (chứng thực "API Key")')
                            ->formatStateUsing(fn (): string => $hasWebhookKey ? 'Đã cấu hình trong .env' : 'Chưa cấu hình - thêm SEPAY_WEBHOOK_KEY vào .env')
                            ->readOnly()
                            ->dehydrated(false)
                            ->prefixIcon($hasWebhookKey ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationTriangle)
                            ->prefixIconColor($hasWebhookKey ? 'success' : 'warning')
                            ->helperText('Khóa là bí mật nên chỉ lưu trong .env, không lưu trong cơ sở dữ liệu.'),
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
