<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
    ]);
});

function fakeGoogleUser(array $overrides = []): void
{
    Socialite::fake('google', GoogleUser::fake([
        'id' => 'google-123',
        'name' => 'Nguyễn Văn An',
        'email' => 'An@Gmail.com',
        'email_verified' => true,
        ...$overrides,
    ]));
}

test('the login and register pages show the google button when configured', function () {
    $this->get(route('login'))->assertSeeText('Tiếp tục với Google')->assertSee(route('auth.google'), false);
    $this->get(route('register'))->assertSeeText('Đăng ký với Google');
});

test('the google button is hidden when google is not configured', function () {
    config(['services.google.client_id' => null]);

    $this->get(route('login'))->assertDontSeeText('Tiếp tục với Google');
    $this->get(route('auth.google'))->assertRedirect(route('login'));
});

test('the google route redirects to google', function () {
    Socialite::fake('google');

    $this->get(route('auth.google'))->assertRedirect();
});

test('a new google user gets a customer account and is logged in', function () {
    fakeGoogleUser();

    $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

    $user = User::sole();
    expect($user->email)->toBe('an@gmail.com')
        ->and($user->name)->toBe('Nguyễn Văn An')
        ->and($user->google_id)->toBe('google-123')
        ->and($user->isAdmin())->toBeFalse()
        ->and($user->hasPassword())->toBeFalse()
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user, 'customer');
    $this->assertGuest('web');
});

test('google links to an existing customer with the same email', function () {
    $user = User::factory()->create(['email' => 'an@gmail.com', 'name' => 'An cũ']);
    fakeGoogleUser();

    $this->get(route('auth.google.callback'))->assertRedirect(route('home'));

    expect(User::count())->toBe(1)
        ->and($user->fresh()->google_id)->toBe('google-123')
        ->and($user->fresh()->name)->toBe('An cũ');
    $this->assertAuthenticatedAs($user, 'customer');
});

test('a returning google user is found by google id even after changing email', function () {
    $user = User::factory()->create(['email' => 'cu@gmail.com']);
    $user->forceFill(['google_id' => 'google-123'])->save();
    fakeGoogleUser(['email' => 'moi@gmail.com']);

    $this->get(route('auth.google.callback'));

    expect(User::count())->toBe(1);
    $this->assertAuthenticatedAs($user, 'customer');
});

test('an admin email cannot log in through google', function () {
    User::factory()->admin()->create(['email' => 'an@gmail.com']);
    fakeGoogleUser();

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Tài khoản quản trị vui lòng đăng nhập tại trang quản trị.']);

    $this->assertGuest('customer');
});

test('an unverified google email is refused', function () {
    fakeGoogleUser(['email_verified' => false]);

    $this->get(route('auth.google.callback'))->assertSessionHasErrors('email');

    expect(User::count())->toBe(0);
    $this->assertGuest('customer');
});

test('declining on the google screen returns to the login page', function () {
    $this->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'Bạn đã huỷ đăng nhập bằng Google.']);

    $this->assertGuest('customer');
});
