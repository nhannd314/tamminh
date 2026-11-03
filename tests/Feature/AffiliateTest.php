<?php

use App\AffiliateCommissionStatus;
use App\AnalysisSection;
use App\Filament\Resources\AffiliateCommissions\Pages\ManageAffiliateCommissions;
use App\Filament\Resources\Affiliates\AffiliateResource;
use App\Filament\Resources\Affiliates\Pages\ManageAffiliates;
use App\Jobs\GenerateAnalysisSection;
use App\Models\Affiliate;
use App\Models\AffiliateCommission;
use App\Models\AnalysisOrder;
use App\Models\FaceAnalysis;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\OrderStatus;
use App\Support\AffiliateReferral;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Queue::fake([GenerateAnalysisSection::class]);
});

/**
 * Đặt một đơn COD 500.000đ tiền hàng (+ phí giao) qua giỏ hàng.
 */
function placeReferredOrder(): Order
{
    $product = Product::factory()->create(['price' => 250000, 'stock' => 10]);

    test()->post(route('cart.store'), ['product_id' => $product->getKey(), 'quantity' => 2]);
    test()->post(route('checkout.store'), [
        'customer_name' => 'Nguyễn Văn An',
        'phone' => '0912345678',
        'address' => '26A Thạch Bàn, Long Biên, Hà Nội',
        'payment_method' => 'cod',
    ]);

    return Order::query()->latest('id')->firstOrFail();
}

describe('landing page', function () {
    test('guests are sent from the header button to the landing page and log in from there', function () {
        Setting::set('affiliate.analysis_commission_percent', 25);
        $user = User::factory()->create();

        $this->get(route('home'))->assertSee('href="'.route('affiliate.landing').'"', false);

        $this->get(route('affiliate.landing'))
            ->assertOk()
            ->assertSeeText(['Đăng nhập để tham gia', '25%', 'Tạo tài khoản'])
            ->assertSee('href="'.route('account.affiliate.show').'"', false);

        $this->get(route('account.affiliate.show'))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('account.affiliate.show'));
    });

    test('creating an account from the landing page leads to the affiliate page', function () {
        $this->get(route('affiliate.landing'))->assertSee('href="'.route('affiliate.register').'"', false);

        $this->get(route('affiliate.register'))->assertRedirect(route('register'));

        $this->post(route('register'), [
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'password' => 'matkhau-123',
            'password_confirmation' => 'matkhau-123',
            'agree_terms' => '1',
        ])->assertRedirect(route('account.affiliate.show'));
    });

    test('logged in customers go straight to their affiliate page', function () {
        $affiliate = Affiliate::factory()->create();

        $this->actingAs($affiliate->user, 'customer')->get(route('home'))
            ->assertSee('href="'.route('account.affiliate.show').'"', false);

        $this->get(route('affiliate.landing'))
            ->assertOk()
            ->assertSeeText('Vào trang affiliate của bạn')
            ->assertDontSeeText('Đăng nhập để tham gia');
    });
});

describe('joining', function () {
    test('guests are sent to the login page', function () {
        $this->get(route('account.affiliate.show'))->assertRedirect(route('login'));
        $this->post(route('account.affiliate.store'))->assertRedirect(route('login'));
    });

    test('a customer sees the program and can join once', function () {
        Setting::set('affiliate.product_commission_percent', 12);
        Setting::set('affiliate.analysis_commission_percent', 20);
        $user = User::factory()->create();

        $this->actingAs($user, 'customer')->get(route('account.affiliate.show'))
            ->assertOk()
            ->assertSeeText(['12%', '20%', 'Tham gia ngay - miễn phí']);

        $this->post(route('account.affiliate.store'))->assertRedirect(route('account.affiliate.show'));
        $this->post(route('account.affiliate.store'));

        $affiliate = Affiliate::sole();
        expect($affiliate->user_id)->toBe($user->id)
            ->and($affiliate->code)->toMatch('/^[A-Z2-9]{6}$/');

        $this->get(route('account.affiliate.show'))
            ->assertOk()
            ->assertSee(route('home', ['ref' => $affiliate->code]), false)
            ->assertSee(route('products.index', ['ref' => $affiliate->code]), false)
            ->assertDontSeeText('Tham gia ngay - miễn phí');
    });

    test('an affiliate can save the bank account for payouts', function () {
        $affiliate = Affiliate::factory()->create();

        $this->actingAs($affiliate->user, 'customer')->put(route('account.affiliate.update'), [
            'bank_name' => ' Vietcombank ',
            'bank_account_number' => '0123 456 789',
            'bank_account_name' => 'nguyễn văn an',
        ])->assertRedirect(route('account.affiliate.show'));

        expect($affiliate->fresh()->only('bank_name', 'bank_account_number', 'bank_account_name'))->toBe([
            'bank_name' => 'Vietcombank',
            'bank_account_number' => '0123456789',
            'bank_account_name' => 'NGUYỄN VĂN AN',
        ]);

        $this->put(route('account.affiliate.update'), ['bank_name' => '', 'bank_account_number' => 'abc', 'bank_account_name' => ''])
            ->assertSessionHasErrors(['bank_name', 'bank_account_number' => 'Số tài khoản gồm 6-20 chữ số.', 'bank_account_name']);
    });
});

describe('referral tracking', function () {
    test('a referral link stores the affiliate code in a cookie', function () {
        $affiliate = Affiliate::factory()->create();

        $this->get(route('products.index', ['ref' => strtolower($affiliate->code)]))
            ->assertOk()
            ->assertCookie(AffiliateReferral::COOKIE, $affiliate->code);
    });

    test('unknown, suspended and own codes are ignored', function () {
        $suspended = Affiliate::factory()->suspended()->create();
        $own = Affiliate::factory()->create();

        $this->get(route('home', ['ref' => 'KHONGCO']))->assertCookieMissing(AffiliateReferral::COOKIE);
        $this->get(route('home', ['ref' => $suspended->code]))->assertCookieMissing(AffiliateReferral::COOKIE);
        $this->actingAs($own->user, 'customer')->get(route('home', ['ref' => $own->code]))->assertCookieMissing(AffiliateReferral::COOKIE);
    });
});

describe('product commissions', function () {
    test('a referred order earns a pending commission on the goods total, approved once completed', function () {
        Setting::set('affiliate.product_commission_percent', 10);
        $affiliate = Affiliate::factory()->create();

        $this->withCookie(AffiliateReferral::COOKIE, $affiliate->code);
        $order = placeReferredOrder();

        expect($order->affiliate_id)->toBe($affiliate->id);

        $commission = AffiliateCommission::sole();
        expect($commission->only('base_amount', 'rate_percent', 'amount'))->toBe(['base_amount' => 500000, 'rate_percent' => 10, 'amount' => 50000])
            ->and($commission->status)->toBe(AffiliateCommissionStatus::Pending);

        $order->update(['status' => OrderStatus::Shipping]);
        expect($commission->fresh()->status)->toBe(AffiliateCommissionStatus::Pending);

        $order->update(['status' => OrderStatus::Completed]);
        expect($commission->fresh()->status)->toBe(AffiliateCommissionStatus::Approved)
            ->and($commission->fresh()->approved_at)->not->toBeNull();
    });

    test('a cancelled order cancels its commission', function () {
        $affiliate = Affiliate::factory()->create();
        $this->withCookie(AffiliateReferral::COOKIE, $affiliate->code);

        $order = placeReferredOrder();
        $order->cancel();

        expect($order->affiliateCommission->status)->toBe(AffiliateCommissionStatus::Cancelled);
    });

    test('orders without a referral or from the affiliate themself earn nothing', function () {
        $affiliate = Affiliate::factory()->create();

        placeReferredOrder();

        $this->actingAs($affiliate->user, 'customer')->withCookie(AffiliateReferral::COOKIE, $affiliate->code);
        $ownOrder = placeReferredOrder();

        expect($ownOrder->affiliate_id)->toBeNull()
            ->and(AffiliateCommission::count())->toBe(0);
    });
});

describe('face analysis commissions', function () {
    test('a paid unlock of a referred analysis earns an approved commission once', function () {
        Setting::set('affiliate.analysis_commission_percent', 20);
        $affiliate = Affiliate::factory()->create();
        $analysis = FaceAnalysis::factory()->create(['affiliate_id' => $affiliate->id]);
        $order = AnalysisOrder::factory()->for($analysis)->section(AnalysisSection::TamDinh, 50000)->create();

        $order->markAsPaid('sepay:1');
        $order->markAsPaid('sepay:1');

        $commission = $order->affiliateCommission;
        expect(AffiliateCommission::count())->toBe(1)
            ->and($commission->amount)->toBe(10000)
            ->and($commission->status)->toBe(AffiliateCommissionStatus::Approved)
            ->and($commission->sourceLabel())->toBe('Xem tướng · '.$order->code);
    });

    test('unlocking attributes an analysis to the referral cookie when it has none', function () {
        $affiliate = Affiliate::factory()->create();
        $analysis = FaceAnalysis::factory()->create();

        $this->withCookie(AffiliateReferral::COOKIE, $affiliate->code)
            ->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh']);

        expect($analysis->fresh()->affiliate_id)->toBe($affiliate->id);
    });

    test('a suspended affiliate earns nothing from later payments', function () {
        $affiliate = Affiliate::factory()->suspended()->create();
        $analysis = FaceAnalysis::factory()->create(['affiliate_id' => $affiliate->id]);

        AnalysisOrder::factory()->for($analysis)->create()->markAsPaid('sepay:2');

        expect(AffiliateCommission::count())->toBe(0);
    });
});

test('the dashboard shows totals and commission history', function () {
    $affiliate = Affiliate::factory()->create();
    $order = Order::factory()->create(['code' => 'DHABCDEFGH']);
    AffiliateCommission::factory()->for($affiliate)->approved()->create(['commissionable_id' => $order->id, 'amount' => 35000]);
    AffiliateCommission::factory()->for($affiliate)->paid()->create(['amount' => 120000]);

    $this->actingAs($affiliate->user, 'customer')->get(route('account.affiliate.show'))
        ->assertOk()
        ->assertSeeText(['35.000đ', '120.000đ', 'Vật phẩm · DHABCDEFGH', 'Chờ chi trả']);
});

describe('admin', function () {
    beforeEach(function () {
        Filament::setCurrentPanel('admin');
    });

    test('regular users cannot open the affiliates screen', function () {
        $this->actingAs(User::factory()->create())
            ->get(AffiliateResource::getUrl('index'))
            ->assertForbidden();
    });

    test('an admin pays out every approved commission of an affiliate', function () {
        $this->actingAs(User::factory()->admin()->create());
        $affiliate = Affiliate::factory()->withBankAccount()->create();
        $approved = AffiliateCommission::factory()->count(2)->for($affiliate)->approved()->create(['amount' => 30000]);
        $pending = AffiliateCommission::factory()->for($affiliate)->create();

        Livewire::test(ManageAffiliates::class)
            ->assertCanSeeTableRecords([$affiliate])
            ->callTableAction('payOut', $affiliate, data: ['note' => 'FT123']);

        foreach ($approved as $commission) {
            expect($commission->fresh()->status)->toBe(AffiliateCommissionStatus::Paid)
                ->and($commission->fresh()->payout_note)->toBe('FT123');
        }

        expect($pending->fresh()->status)->toBe(AffiliateCommissionStatus::Pending);
    });

    test('an admin can suspend an affiliate and cancel a commission', function () {
        $this->actingAs(User::factory()->admin()->create());
        $affiliate = Affiliate::factory()->create();
        $commission = AffiliateCommission::factory()->for($affiliate)->approved()->create();

        Livewire::test(ManageAffiliates::class)->callTableAction('suspend', $affiliate);
        Livewire::test(ManageAffiliateCommissions::class)->callTableAction('cancel', $commission);

        expect($affiliate->fresh()->isActive())->toBeFalse()
            ->and($commission->fresh()->status)->toBe(AffiliateCommissionStatus::Cancelled);
    });
});
