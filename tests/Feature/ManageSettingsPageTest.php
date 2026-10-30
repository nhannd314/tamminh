<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
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
        ->assertSeeText('Thanh toán (SePay)');
});

test('regular users cannot open the settings page', function () {
    $this->actingAs(User::factory()->create())
        ->get(ManageSettings::getUrl())
        ->assertForbidden();
});

test('the form is filled from the settings table', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->assertSet('data.full_package', 199000)
        ->assertSet('data.price_thap_nhi_cung', 69000)
        ->assertSet('data.free_sections', ['ngu_hanh'])
        ->assertSet('data.transfer_content', '{code}');
});

test('saving writes every field back to the settings table', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.price_tam_dinh', '59.000')
        ->set('data.full_package', '149.000')
        ->set('data.upgrade_credit_percent', 30)
        ->set('data.free_sections', ['ngu_hanh', 'than_khi_sac'])
        ->set('data.bank', 'Vietcombank')
        ->set('data.account_number', '0123456789')
        ->set('data.account_name', 'Công ty Tâm Minh')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified('Đã lưu cấu hình');

    expect(Setting::get('pricing.section.tam_dinh'))->toBe(59000)
        ->and(Setting::get('pricing.full_package'))->toBe(149000)
        ->and(Setting::get('pricing.upgrade_credit_percent'))->toBe(30)
        ->and(Setting::get('analysis.free_sections'))->toBe(['ngu_hanh', 'than_khi_sac'])
        ->and(Setting::get('payment.bank'))->toBe('Vietcombank')
        ->and(Setting::get('payment.account_name'))->toBe('CONG TY TAM MINH');
});

test('invalid payment settings are rejected', function (string $field, mixed $value) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageSettings::class)
        ->set('data.account_number', '0123456789')
        ->set('data.account_name', 'TAM MINH')
        ->set("data.{$field}", $value)
        ->call('save')
        ->assertHasErrors("data.{$field}");
})->with([
    'content without code' => ['transfer_content', 'TAMMINH'],
    'account number with letters' => ['account_number', '01234ABC'],
    'percent over 100' => ['upgrade_credit_percent', 120],
    'missing full package price' => ['full_package', ''],
]);
