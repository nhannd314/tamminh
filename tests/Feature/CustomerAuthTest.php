<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('the customer auth pages render', function () {
    $this->get(route('login'))->assertOk()->assertSeeText('Đăng nhập')->assertSee(route('password.request'), false);
    $this->get(route('register'))->assertOk()->assertSeeText('Tạo tài khoản');
    $this->get(route('password.request'))->assertOk()->assertSeeText('Quên mật khẩu?');
});

test('a customer can register and is logged in on the customer guard only', function () {
    $this->post(route('register'), [
        'name' => 'Nguyễn Văn An',
        'email' => 'an@example.com',
        'password' => 'matkhau-123',
        'password_confirmation' => 'matkhau-123',
        'agree_terms' => '1',
    ])->assertRedirect(route('home'));

    $user = User::where('email', 'an@example.com')->firstOrFail();

    expect($user->isAdmin())->toBeFalse();
    $this->assertAuthenticatedAs($user, 'customer');
    $this->assertGuest('web');
});

test('a registration cannot reuse an email or mismatch the password', function () {
    User::factory()->create(['email' => 'an@example.com']);

    $this->post(route('register'), [
        'name' => 'An',
        'email' => 'an@example.com',
        'password' => 'matkhau-123',
        'password_confirmation' => 'khac-nhau',
    ])->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest('customer');
});

test('a customer can log in and out', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user, 'customer');
    $this->assertGuest('web');

    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest('customer');
});

test('guests get an account dropdown with login and register', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['data-dropdown-trigger', 'Tài khoản', 'data-dropdown-menu', 'href="'.route('login').'"', 'Đăng nhập', 'href="'.route('register').'"', 'Đăng ký tài khoản'], false)
        ->assertDontSee('action="'.route('logout').'"', false);
});

test('logged in customers get an account dropdown with their avatar, account pages and logout', function () {
    $user = User::factory()->create(['name' => 'Nguyễn Văn ánh']);

    $this->actingAs($user, 'customer')->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['data-cart-button', 'data-dropdown-trigger', 'Tài khoản', 'data-dropdown-menu', 'data-user-avatar', '>Á</span>'], false)
        ->assertSeeInOrder([
            route('account.show'),
            route('account.edit'),
            route('account.password.edit'),
            route('account.affiliate.show'),
            'action="'.route('logout').'"',
        ], false);
});

test('the avatar initial falls back to the email when the name is blank', function () {
    $user = User::factory()->make(['name' => '  ', 'email' => 'binh@example.com']);

    expect($user->avatar_initial)->toBe('B');
});

test('a wrong password is rejected', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'sai-mat-khau'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

test('an admin cannot log in through the customer form', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('customer');
});

test('a customer session does not grant access to the admin panel', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'customer')->get('/admin')->assertRedirect('/admin/login');
});

test('registration stores the optional phone and newsletter choice', function () {
    $this->post(route('register'), [
        'name' => 'Nguyễn Văn An',
        'email' => 'an@example.com',
        'phone' => '0912 345 678',
        'password' => 'matkhau-123',
        'password_confirmation' => 'matkhau-123',
        'agree_terms' => '1',
        'newsletter' => '1',
    ])->assertRedirect(route('home'));

    $user = User::where('email', 'an@example.com')->firstOrFail();

    expect($user->phone)->toBe('0912345678')
        ->and($user->newsletter_opt_in)->toBeTrue();
});

test('registration requires accepting the terms and a valid phone', function () {
    $this->post(route('register'), [
        'name' => 'An',
        'email' => 'an@example.com',
        'phone' => '12345',
        'password' => 'matkhau-123',
        'password_confirmation' => 'matkhau-123',
    ])->assertSessionHasErrors([
        'agree_terms' => 'Bạn cần đồng ý với điều khoản để tiếp tục.',
        'phone' => 'Số điện thoại chưa đúng.',
    ]);

    $this->assertGuest('customer');
});

test('requesting a reset link emails customers and shows the check inbox step', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'an@example.com']);

    $this->post(route('password.email'), ['email' => 'an@example.com'])
        ->assertRedirect(route('password.request'));

    Notification::assertSentTo($user, ResetPassword::class);

    $this->followingRedirects()
        ->post(route('password.email'), ['email' => 'khong-ton-tai@example.com'])
        ->assertSeeText('Kiểm tra hộp thư')
        ->assertSeeText('khong-ton-tai@example.com');
});

test('admins cannot reset their password through the customer form', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->post(route('password.email'), ['email' => $admin->email])->assertRedirect(route('password.request'));

    Notification::assertNothingSent();
});

test('a customer can set a new password from the emailed link', function () {
    $user = User::factory()->create(['email' => 'an@example.com']);
    $token = Password::broker()->createToken($user);

    $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertSeeText('Đặt mật khẩu mới');

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'matkhau-moi-123',
        'password_confirmation' => 'matkhau-moi-123',
    ])->assertRedirect(route('password.request'));

    expect(Hash::check('matkhau-moi-123', $user->fresh()->password))->toBeTrue();

    $this->get(route('password.request'))->assertSeeText('Đã đổi mật khẩu');
});

test('an invalid reset token is rejected', function () {
    $user = User::factory()->create();

    $this->post(route('password.update'), [
        'token' => 'sai-token',
        'email' => $user->email,
        'password' => 'matkhau-moi-123',
        'password_confirmation' => 'matkhau-moi-123',
    ])->assertSessionHasErrors('token');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
