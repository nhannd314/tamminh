<?php

use App\Filament\Resources\Experts\ExpertResource;
use App\Models\Expert;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('expert page shows contact details, social channels and a zalo message button', function () {
    $expert = Expert::factory()->create([
        'name' => 'Thầy Minh Quang',
        'email' => 'quang@example.com',
        'phone' => '0912 345 678',
        'address' => '12 Lê Lợi, Hà Nội',
        'description' => 'Mô tả chi tiết về chuyên gia.',
        'tiktok_url' => 'https://tiktok.com/@quang',
        'facebook_url' => 'https://facebook.com/quang',
        'youtube_url' => null,
        'working_hours' => 'Thứ 2 – Thứ 7',
        'specialties' => ['Chỉ tay'],
        'services' => [['name' => 'Xem tướng trực tiếp', 'description' => '', 'duration' => '60 phút', 'price' => '800.000đ']],
        'reviews' => [['rating' => 5, 'name' => 'Chị Thu Hà', 'location' => 'Hà Nội', 'quote' => 'Rất đúng.']],
    ]);

    $this->get(route('experts.show', $expert))
        ->assertOk()
        ->assertSeeText('quang@example.com')
        ->assertSeeText('12 Lê Lợi, Hà Nội')
        ->assertSeeText('Mô tả chi tiết về chuyên gia.')
        ->assertSee('https://zalo.me/84912345678', false)
        ->assertSee('https://tiktok.com/@quang', false)
        ->assertSee('https://facebook.com/quang', false)
        ->assertSeeText('Thứ 2 – Thứ 7')
        ->assertSeeText('Chỉ tay')
        ->assertSeeText('800.000đ')
        ->assertSeeText('Chị Thu Hà')
        ->assertDontSeeText('YouTube');
});

test('expert page hides the zalo button when there is no phone number', function () {
    $expert = Expert::factory()->create(['phone' => null]);

    $this->get(route('experts.show', $expert))
        ->assertOk()
        ->assertDontSeeText('Nhắn tin qua Zalo');
});

test('inactive experts return 404', function () {
    $expert = Expert::factory()->inactive()->create();

    $this->get(route('experts.show', $expert))->assertNotFound();
});

test('an expert can exist without a user account', function () {
    expect(Expert::factory()->create()->user_id)->toBeNull();

    $user = User::factory()->create();
    expect(Expert::factory()->create(['user_id' => $user->id])->user->is($user))->toBeTrue();
});

test('admins can open the expert screen', function () {
    Filament::setCurrentPanel('admin');

    $this->actingAs(User::factory()->admin()->create())
        ->get(ExpertResource::getUrl('index'))
        ->assertOk();
});
