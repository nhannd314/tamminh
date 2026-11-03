<?php

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\OrderPaymentMethod;
use App\OrderStatus;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();

    config([
        'services.sepay.webhook_key' => 'secret-key',
        'services.sepay.bank' => 'MBBank',
        'services.sepay.account_number' => '0123456789',
        'services.sepay.account_name' => 'Công ty Tâm Minh',
        'services.sepay.transfer_content' => '{code}',
    ]);
});

function cartWith(Product $product, int $quantity = 1): void
{
    test()->post(route('cart.store'), ['product_id' => $product->getKey(), 'quantity' => $quantity]);
}

/**
 * @return array<string, string>
 */
function checkoutPayload(array $overrides = []): array
{
    return [
        'customer_name' => 'Nguyễn Văn An',
        'phone' => '0912 345 678',
        'address' => '26A Thạch Bàn, Long Biên, Hà Nội',
        'note' => 'Giao giờ hành chính',
        'payment_method' => 'cod',
        ...$overrides,
    ];
}

test('the checkout page redirects to the cart when the cart is empty', function () {
    $this->get(route('checkout.show'))->assertRedirect(route('cart.show'));
});

test('the checkout page shows both payment options with their shipping fees', function () {
    cartWith(Product::factory()->create(['name' => 'Vòng trầm hương', 'price' => 500000]));

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSeeText('Vòng trầm hương')
        ->assertSeeText('Chuyển khoản online')
        ->assertSeeText('Miễn phí giao hàng')
        ->assertSeeText('Thanh toán khi nhận hàng (COD)')
        ->assertSeeText('Phí giao hàng 30.000đ');
});

test('a cod order charges the default shipping fee, keeps stock in check and emails admins', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Vòng trầm hương', 'price' => 500000, 'stock' => 5]);
    cartWith($product, 2);

    $response = $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::sole();
    $response->assertRedirect(route('orders.show', $order));

    expect($order->payment_method)->toBe(OrderPaymentMethod::Cod)
        ->and($order->status)->toBe(OrderStatus::New)
        ->and($order->phone)->toBe('0912345678')
        ->and($order->subtotal)->toBe(1000000)
        ->and($order->shipping_fee)->toBe(30000)
        ->and($order->total)->toBe(1030000)
        ->and($order->isPaid())->toBeFalse()
        ->and($order->items->sole()->only('product_name', 'quantity', 'unit_price'))
        ->toBe(['product_name' => 'Vòng trầm hương', 'quantity' => 2, 'unit_price' => 500000])
        ->and($product->fresh()->stock)->toBe(3);

    $this->get(route('cart.show'))->assertSeeText('Giỏ hàng đang trống');

    Mail::assertQueued(OrderPlaced::class, fn (OrderPlaced $mail): bool => $mail->order->is($order) && $mail->hasTo($admin->email));
});

test('an order placed while logged in belongs to the customer', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer, 'customer');
    cartWith(Product::factory()->create(['stock' => 5]));

    $this->post(route('checkout.store'), checkoutPayload());

    expect(Order::sole()->user_id)->toBe($customer->id);
});

test('the cod shipping fee follows the admin setting', function () {
    Setting::set('shop.shipping_fee', 45000);
    cartWith(Product::factory()->create(['price' => 200000]));

    $this->post(route('checkout.store'), checkoutPayload());

    expect(Order::sole()->total)->toBe(245000);
});

test('an online order ships free and shows the transfer qr until paid', function () {
    cartWith(Product::factory()->create(['price' => 500000]));

    $this->post(route('checkout.store'), checkoutPayload(['payment_method' => 'online']));

    $order = Order::sole();
    expect($order->shipping_fee)->toBe(0)
        ->and($order->total)->toBe(500000);

    $this->get(route('orders.show', $order))
        ->assertOk()
        ->assertSeeText('chờ thanh toán')
        ->assertSee('data-payment-watch', false)
        ->assertSee('amount=500000', false)
        ->assertSeeText($order->code);

    $this->getJson(route('orders.payment-status', $order))->assertExactJson(['paid' => false]);
});

test('the sepay webhook marks an online order as paid', function () {
    $order = Order::factory()->online()->create(['subtotal' => 500000, 'total' => 500000]);

    $this->postJson(route('webhooks.sepay'), [
        'id' => 501,
        'content' => 'NGUYEN VAN A chuyen tien '.$order->code,
        'transferType' => 'in',
        'transferAmount' => 500000,
    ], ['Authorization' => 'Apikey secret-key'])->assertExactJson(['success' => true]);

    expect($order->fresh()->isPaid())->toBeTrue()
        ->and($order->fresh()->transaction_id)->toBe('sepay:501');

    $this->getJson(route('orders.payment-status', $order))->assertExactJson(['paid' => true]);
    $this->get(route('orders.show', $order))
        ->assertSeeText('Cảm ơn bạn đã đặt hàng!')
        ->assertDontSee('data-payment-watch', false);
});

test('the sepay webhook ignores an underpaid online order', function () {
    $order = Order::factory()->online()->create(['subtotal' => 500000, 'total' => 500000]);

    $this->postJson(route('webhooks.sepay'), [
        'id' => 502,
        'content' => $order->code,
        'transferType' => 'in',
        'transferAmount' => 400000,
    ], ['Authorization' => 'Apikey secret-key'])->assertOk();

    expect($order->fresh()->isPaid())->toBeFalse();
});

test('checkout sends the customer back to the cart when the item sold out', function () {
    $product = Product::factory()->create(['stock' => 2]);
    cartWith($product, 2);
    $product->update(['stock' => 0]);

    $this->post(route('checkout.store'), checkoutPayload())->assertRedirect(route('cart.show'));

    expect(Order::count())->toBe(0);
    Mail::assertNothingQueued();
});

test('checkout requires the delivery details and a payment method', function () {
    cartWith(Product::factory()->create());

    $this->post(route('checkout.store'), [])
        ->assertSessionHasErrors([
            'customer_name' => 'Vui lòng nhập họ và tên người nhận.',
            'phone' => 'Vui lòng nhập số điện thoại.',
            'address' => 'Vui lòng nhập địa chỉ nhận hàng.',
            'payment_method' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

    expect(Order::count())->toBe(0);
});

test('checkout rejects an invalid phone number or payment method', function (array $overrides, string $field, string $message) {
    cartWith(Product::factory()->create());

    $this->post(route('checkout.store'), checkoutPayload($overrides))
        ->assertSessionHasErrors([$field => $message]);
})->with([
    'phone with letters' => [['phone' => '09abc45678'], 'phone', 'Số điện thoại không hợp lệ.'],
    'unknown payment method' => [['payment_method' => 'bitcoin'], 'payment_method', 'Hình thức thanh toán không hợp lệ.'],
]);

describe('admin', function () {
    beforeEach(function () {
        Filament::setCurrentPanel('admin');
    });

    test('admins see orders in the admin panel', function () {
        $order = Order::factory()->create(['customer_name' => 'Lê Văn Cường']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(OrderResource::getUrl('index'))
            ->assertOk()
            ->assertSeeText($order->code)
            ->assertSeeText('Lê Văn Cường');
    });

    test('regular users cannot open the orders screen', function () {
        $this->actingAs(User::factory()->create())
            ->get(OrderResource::getUrl('index'))
            ->assertForbidden();
    });

    test('an admin can move an order along and record payment', function () {
        $this->actingAs(User::factory()->admin()->create());
        $order = Order::factory()->create();

        Livewire::test(ManageOrders::class)
            ->callTableAction('changeStatus', $order, ['status' => OrderStatus::Shipping->value])
            ->callTableAction('markPaid', $order);

        expect($order->fresh()->status)->toBe(OrderStatus::Shipping)
            ->and($order->fresh()->isPaid())->toBeTrue();
    });

    test('cancelling an order returns its items to stock once', function () {
        $this->actingAs(User::factory()->admin()->create());
        $product = Product::factory()->create(['stock' => 3]);
        $order = Order::factory()->create();
        OrderItem::factory()->for($order)->create(['product_id' => $product->id, 'quantity' => 2]);

        Livewire::test(ManageOrders::class)->callTableAction('cancel', $order);
        $order->fresh()->cancel();

        expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
            ->and($product->fresh()->stock)->toBe(5);
    });
});
