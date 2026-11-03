<?php

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use App\UserRole;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('admin can create a user with a role and a hashed password', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManageUsers::class)
        ->callAction('create', [
            'name' => 'Lê Văn Bình',
            'email' => 'binh@example.com',
            'role' => UserRole::Admin->value,
            'password' => 'matkhau-123',
        ])
        ->assertHasNoActionErrors();

    $user = User::query()->where('email', 'binh@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->password)->not->toBe('matkhau-123');
});

test('editing a user keeps the password when left blank and can change the role', function () {
    $this->actingAs(User::factory()->admin()->create());
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::test(ManageUsers::class)
        ->callAction(TestAction::make('edit')->table($user), [
            'name' => 'Tên mới',
            'email' => $user->email,
            'role' => UserRole::Admin->value,
            'password' => '',
        ])
        ->assertHasNoActionErrors();

    $user->refresh();

    expect($user->name)->toBe('Tên mới')
        ->and($user->role)->toBe(UserRole::Admin)
        ->and($user->password)->toBe($originalPassword);
});

test('admin cannot demote or delete their own account', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(ManageUsers::class)
        ->callAction(TestAction::make('edit')->table($admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::User->value,
        ])
        ->assertTableActionHidden('delete', $admin);

    expect($admin->refresh()->role)->toBe(UserRole::Admin);
});

test('email must be unique', function () {
    $this->actingAs(User::factory()->admin()->create());
    $existing = User::factory()->create();

    Livewire::test(ManageUsers::class)
        ->callAction('create', [
            'name' => 'Trùng email',
            'email' => $existing->email,
            'role' => UserRole::User->value,
            'password' => 'matkhau-123',
        ])
        ->assertHasActionErrors(['email' => 'unique']);
});
