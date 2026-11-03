<?php

use App\Filament\Pages\ManageSettings;
use App\Models\FaceAnalysis;
use App\Models\Setting;
use App\Models\User;
use App\Support\ShopContact;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('admins can open the settings page', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(ManageSettings::getUrl())
        ->assertOk()
        ->assertSeeText('Cấu hình hệ thống')
        ->assertSeeText('Bảng giá')
        ->assertDontSeeText('SePay');
});

test('regular users cannot open the settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get(ManageSettings::getUrl())
        ->assertForbidden();
});

test('the form is filled from the settings table', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->assertSet('data.price_ngu_hanh', 0)
        ->assertSet('data.price_thap_nhi_cung', 20000)
        ->assertSet('data.full_package_discount_percent', 20);
});

test('saving writes every field back to the settings table', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.price_tam_dinh', '59.000')
        ->set('data.full_package_discount_percent', 30)
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified('Đã lưu cấu hình');

    expect(Setting::get('pricing.section.tam_dinh'))->toBe(59000)
        ->and(Setting::get('pricing.full_package_discount_percent'))->toBe(30);
});

test('invalid settings are rejected', function (string $field, mixed $value) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set("data.{$field}", $value)
        ->call('save')
        ->assertHasErrors("data.{$field}");
})->with([
    'percent over 100' => ['full_package_discount_percent', 120],
    'missing section price' => ['price_tam_dinh', ''],
]);

test('testimonials and faqs start from the site config defaults', function () {
    config([
        'site.testimonials' => [
            ['rating' => 5, 'quote' => 'Mặc định.', 'name' => 'Khách Mặc Định', 'role' => 'Hà Nội', 'service' => 'Xem tướng', 'avatar' => 'K', 'tone' => 'navy'],
        ],
        'site.faqs' => [['question' => 'Hỏi mặc định?', 'answer' => 'Đáp mặc định.']],
    ]);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->assertSet('data.testimonials', fn (array $items): bool => array_column($items, 'name') === ['Khách Mặc Định'])
        ->assertSet('data.faqs', fn (array $items): bool => array_column($items, 'question') === ['Hỏi mặc định?']);
});

test('saved testimonials and faqs are shown on the home page', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.testimonials', [
            ['rating' => '4', 'quote' => '  Thầy xem rất kỹ.  ', 'name' => 'Đỗ Minh Châu', 'role' => 'Huế', 'service' => 'Xem chỉ tay', 'avatar' => 'C', 'tone' => 'copper'],
        ])
        ->set('data.faqs', [
            ['question' => 'Ảnh lưu bao lâu?', 'answer' => 'Ảnh xóa sau :portrait_days ngày.'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::get('home.testimonials'))->toBe([
        ['rating' => 4, 'quote' => 'Thầy xem rất kỹ.', 'name' => 'Đỗ Minh Châu', 'role' => 'Huế', 'service' => 'Xem chỉ tay', 'avatar' => 'C', 'tone' => 'copper'],
    ]);

    $this->get(route('home'))
        ->assertSeeText('Thầy xem rất kỹ.')
        ->assertSeeText('Đỗ Minh Châu')
        ->assertSeeText('Ảnh lưu bao lâu?')
        ->assertSeeText('Ảnh xóa sau '.FaceAnalysis::PORTRAIT_RETENTION_DAYS.' ngày.');
});

test('a testimonial without a quote is rejected', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.testimonials', [
            ['rating' => 5, 'quote' => '', 'name' => 'Khách', 'role' => 'Hà Nội', 'service' => 'Xem tướng', 'avatar' => 'K', 'tone' => 'navy'],
        ])
        ->call('save')
        ->assertHasErrors();

    expect(Setting::get('home.testimonials'))->toBeNull();
});

test('contact details default to the site config', function () {
    config(['site.phone' => '0900-111-222', 'site.zalo' => '0900-333-444']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->assertSet('data.contact_phone', '0900-111-222')
        ->assertSet('data.contact_zalo', '0900-333-444');
});

test('saved contact details replace the config across the site', function () {
    config(['site.phone' => '0900-111-222', 'site.zalo' => '0900-111-222']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.contact_phone', '1900-6868')
        ->set('data.contact_zalo', '0987 654 321')
        ->set('data.contact_email', 'hotro@tamminh.test')
        ->set('data.contact_address', '1 Tràng Tiền, Hà Nội')
        ->call('save')
        ->assertHasNoErrors();

    expect(ShopContact::hotline())->toBe('1900-6868')
        ->and(ShopContact::zaloUrl())->toBe('https://zalo.me/84987654321');

    $this->get(route('home'))
        ->assertSeeText('1900-6868')
        ->assertSeeText('hotro@tamminh.test')
        ->assertSeeText('1 Tràng Tiền, Hà Nội')
        ->assertSee('https://zalo.me/84987654321', false);
});

test('saved social links show in the footer and blank ones are hidden', function () {
    config(['site.facebook' => '', 'site.youtube' => '', 'site.tiktok' => '', 'site.zalo' => '0900-111-222']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.contact_facebook', 'https://facebook.com/tamminh')
        ->set('data.contact_tiktok', 'https://tiktok.com/@tamminh')
        ->call('save')
        ->assertHasNoErrors();

    expect(ShopContact::socialLinks())->toBe([
        'facebook' => 'https://facebook.com/tamminh',
        'tiktok' => 'https://tiktok.com/@tamminh',
        'zalo' => 'https://zalo.me/84900111222',
    ]);

    $this->get(route('home'))
        ->assertSee('href="https://facebook.com/tamminh"', false)
        ->assertSee('href="https://tiktok.com/@tamminh"', false)
        ->assertDontSee('aria-label="Youtube"', false);
});

test('social links must be valid urls', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.contact_facebook', 'facebook tam minh')
        ->call('save')
        ->assertHasErrors('data.contact_facebook');
});

test('an invalid contact email is rejected', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.contact_email', 'khong-phai-email')
        ->call('save')
        ->assertHasErrors('data.contact_email');
});
