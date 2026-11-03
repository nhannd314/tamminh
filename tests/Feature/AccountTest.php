<?php

use App\Models\FaceAnalysis;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PalmReadingBooking;
use App\Models\Product;
use App\Models\User;
use App\PalmReadingPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('guests are sent to the login page', function () {
    $this->get(route('account.show'))->assertRedirect(route('login'));
    $this->get(route('account.edit'))->assertRedirect(route('login'));
    $this->put(route('account.update'), ['name' => 'An'])->assertRedirect(route('login'));
    $this->get(route('account.password.edit'))->assertRedirect(route('login'));
});

test('the edit page is prefilled with the current details', function () {
    $user = User::factory()->create(['name' => 'Nguyễn Văn An', 'phone' => '0912345678', 'address' => '26A Thạch Bàn, Hà Nội']);

    $this->actingAs($user, 'customer')->get(route('account.edit'))
        ->assertOk()
        ->assertSee('value="Nguyễn Văn An"', false)
        ->assertSee('value="0912345678"', false)
        ->assertSee('value="26A Thạch Bàn, Hà Nội"', false)
        ->assertSee('action="'.route('account.update').'"', false);
});

test('a customer can update their details and shipping address but not their email', function () {
    $user = User::factory()->create(['email' => 'an@example.com', 'newsletter_opt_in' => true]);

    $this->actingAs($user, 'customer')->put(route('account.update'), [
        'name' => '  Trần   Thị Bình ',
        'email' => 'khac@example.com',
        'phone' => '0987 654 321',
        'address' => '12 Láng Hạ, Đống Đa, Hà Nội',
    ])->assertRedirect(route('account.edit'))->assertSessionHas('toast', 'Đã lưu thông tin cá nhân.');

    expect($user->fresh()->only('name', 'email', 'phone', 'address', 'newsletter_opt_in'))->toBe([
        'name' => 'Trần Thị Bình',
        'email' => 'an@example.com',
        'phone' => '0987654321',
        'address' => '12 Láng Hạ, Đống Đa, Hà Nội',
        'newsletter_opt_in' => false,
    ]);
});

test('updating details requires a name and a valid phone', function () {
    $user = User::factory()->create(['name' => 'An']);

    $this->actingAs($user, 'customer')->put(route('account.update'), ['name' => '', 'phone' => '12345'])
        ->assertSessionHasErrors(['name' => 'Vui lòng nhập họ tên.', 'phone' => 'Số điện thoại chưa đúng.']);

    expect($user->fresh()->name)->toBe('An');
});

test('a customer can change their password with the current one', function () {
    $user = User::factory()->create(['remember_token' => 'old-token']);

    $this->actingAs($user, 'customer')->put(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'matkhau-moi-123',
        'password_confirmation' => 'matkhau-moi-123',
    ])->assertRedirect(route('account.password.edit'))->assertSessionHas('toast', 'Đã đổi mật khẩu.');

    $user->refresh();
    expect(Hash::check('matkhau-moi-123', $user->password))->toBeTrue()
        ->and($user->remember_token)->not->toBe('old-token');
    $this->assertAuthenticatedAs($user, 'customer');
});

test('changing the password rejects a wrong current password or a weak new one', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'customer')->put(route('account.password.update'), [
        'current_password' => 'sai-mat-khau',
        'password' => 'ngan',
        'password_confirmation' => 'khac',
    ])->assertSessionHasErrors([
        'current_password' => 'Mật khẩu hiện tại chưa đúng.',
        'password' => 'Mật khẩu cần ít nhất 8 ký tự.',
    ]);

    $this->put(route('account.password.update'), ['password' => 'matkhau-moi-123', 'password_confirmation' => 'matkhau-moi-123'])
        ->assertSessionHasErrors(['current_password' => 'Vui lòng nhập mật khẩu hiện tại.']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('a google account without a password can set one and then log in with it', function () {
    $user = User::factory()->create(['email' => 'an@example.com', 'password' => null]);

    $this->actingAs($user, 'customer')->get(route('account.password.edit'))
        ->assertOk()
        ->assertSeeText('Đặt mật khẩu')
        ->assertDontSee('name="current_password"', false);

    $this->put(route('account.password.update'), ['password' => 'matkhau-moi-123', 'password_confirmation' => 'matkhau-moi-123'])
        ->assertSessionHas('toast', 'Đã đặt mật khẩu.');

    $this->post(route('logout'));
    $this->post(route('login'), ['email' => 'an@example.com', 'password' => 'matkhau-moi-123'])->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user, 'customer');
});

test('an account without a password cannot be logged into with any password', function () {
    User::factory()->create(['email' => 'an@example.com', 'password' => null]);

    $this->post(route('login'), ['email' => 'an@example.com', 'password' => ''])->assertSessionHasErrors('password');
    $this->post(route('login'), ['email' => 'an@example.com', 'password' => 'bat-ky'])->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

test('checkout is prefilled with the saved shipping details', function () {
    $user = User::factory()->create(['name' => 'Nguyễn Văn An', 'phone' => '0912345678', 'address' => '26A Thạch Bàn, Hà Nội']);
    $this->actingAs($user, 'customer')->post(route('cart.store'), ['product_id' => Product::factory()->create()->getKey(), 'quantity' => 1]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('value="Nguyễn Văn An"', false)
        ->assertSee('value="0912345678"', false)
        ->assertSee('value="26A Thạch Bàn, Hà Nội"', false);
});

test('the account page shows the profile, orders and registered services', function () {
    $user = User::factory()->create(['name' => 'Nguyễn Văn An', 'email' => 'an@example.com', 'phone' => '0912345678']);

    $order = Order::factory()->for($user)->create(['total' => 530000]);
    OrderItem::factory()->for($order)->create(['quantity' => 2]);
    $analysis = FaceAnalysis::factory()->for($user)->create(['name' => 'Lượt xem của An']);
    PalmReadingBooking::factory()->for($user)->create(['package' => PalmReadingPackage::Premium]);

    $this->actingAs($user, 'customer')->get(route('account.show'))
        ->assertOk()
        ->assertSeeText(['Nguyễn Văn An', 'an@example.com', '0912345678'])
        ->assertSeeText([$order->code, '530.000đ', '2 vật phẩm'])
        ->assertSee(route('orders.show', $order), false)
        ->assertSeeText('Lượt xem của An')
        ->assertSee(route('analyses.show', $analysis), false)
        ->assertSeeText('Gói Chuyên sâu');
});

test('the account page never lists another customer\'s records', function () {
    $other = User::factory()->create();
    $otherOrder = Order::factory()->for($other)->create();
    FaceAnalysis::factory()->for($other)->create(['name' => 'Lượt xem người khác']);
    $guestOrder = Order::factory()->create();

    $this->actingAs(User::factory()->create(), 'customer')->get(route('account.show'))
        ->assertOk()
        ->assertDontSeeText([$otherOrder->code, $guestOrder->code, 'Lượt xem người khác'])
        ->assertSeeText(['Bạn chưa có đơn hàng nào.', 'Bạn chưa có lượt xem tướng nào.', 'Bạn chưa đặt lịch xem chỉ tay.']);
});
