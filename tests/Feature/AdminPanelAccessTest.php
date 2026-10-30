<?php

use App\Models\User;
use App\UserRole;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('new users get the user role by default', function () {
    $user = User::create(['name' => 'Khách', 'email' => 'khach@example.com', 'password' => 'secret-password']);

    expect($user->refresh()->role)->toBe(UserRole::User)
        ->and($user->isAdmin())->toBeFalse();
});

test('role cannot be mass assigned', function () {
    $user = User::create(['name' => 'Khách', 'email' => 'khach@example.com', 'password' => 'secret-password', 'role' => 'admin']);

    expect($user->refresh()->role)->toBe(UserRole::User);
});

test('admins can open the filament panel', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk();
});

test('regular users are refused by the filament panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('guests are sent to the filament login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('admin seeder creates a single admin account and can be rerun', function () {
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admin = User::sole();

    expect($admin->email)->toBe('nhannd314@gmail.com')
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and(Hash::check('MatKhau@314!', $admin->password))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});
