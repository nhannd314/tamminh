<?php

use App\Filament\Resources\PalmReadingBookings\Pages\ManagePalmReadingBookings;
use App\Filament\Resources\PalmReadingBookings\PalmReadingBookingResource;
use App\Gender;
use App\Mail\PalmReadingBooked;
use App\Models\PalmReadingBooking;
use App\Models\User;
use App\PalmReadingPackage;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * @return array<string, string|int>
 */
function palmReadingPayload(array $overrides = []): array
{
    return [
        'palm_name' => 'Nguyễn Văn An',
        'palm_gender' => 'female',
        'palm_birth_day' => 15,
        'palm_birth_month' => 8,
        'palm_birth_year' => 1990,
        'palm_phone' => '0912 345 678',
        'palm_package' => 'premium',
        ...$overrides,
    ];
}

test('a visitor can book a palm reading and admins are emailed', function () {
    Mail::fake();
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'khach@example.com']);

    $response = $this->post(route('palm-readings.store'), palmReadingPayload());

    $response->assertRedirect(route('home'))
        ->assertSessionHas('toast', 'Đã nhận yêu cầu đặt lịch - Tâm Minh sẽ liên hệ xác nhận sớm nhất.');

    $booking = PalmReadingBooking::sole();
    expect($booking->name)->toBe('Nguyễn Văn An')
        ->and($booking->gender)->toBe(Gender::Female)
        ->and($booking->birth_date->toDateString())->toBe('1990-08-15')
        ->and($booking->phone)->toBe('0912345678')
        ->and($booking->package)->toBe(PalmReadingPackage::Premium)
        ->and($booking->contacted_at)->toBeNull();

    Mail::assertQueued(PalmReadingBooked::class, fn (PalmReadingBooked $mail): bool => $mail->booking->is($booking)
        && $mail->hasTo($admin->email)
        && ! $mail->hasTo('khach@example.com'));
});

test('a booking made while logged in belongs to the customer', function () {
    Mail::fake();
    $customer = User::factory()->create();

    $this->actingAs($customer, 'customer')->post(route('palm-readings.store'), palmReadingPayload());

    expect(PalmReadingBooking::sole()->user_id)->toBe($customer->id);
});

test('the booking email lists the customer details', function () {
    $booking = PalmReadingBooking::factory()->create([
        'name' => 'Trần Thị Bình',
        'phone' => '0987654321',
        'birth_date' => '1985-03-02',
        'package' => PalmReadingPackage::Basic,
    ]);

    (new PalmReadingBooked($booking))
        ->assertHasSubject('Lịch xem chỉ tay mới: Trần Thị Bình - 0987654321')
        ->assertSeeInHtml('02/03/1985')
        ->assertSeeInHtml('Cơ bản (miễn phí)')
        ->assertSeeInHtml(PalmReadingBookingResource::getUrl(panel: 'admin'));
});

test('a booking requires every field', function () {
    Mail::fake();

    $this->post(route('palm-readings.store'), [])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors([
            'palm_name' => 'Vui lòng nhập họ và tên.',
            'palm_gender' => 'Vui lòng chọn giới tính.',
            'palm_birth_day' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'palm_phone' => 'Vui lòng nhập số điện thoại.',
            'palm_package' => 'Vui lòng chọn gói xem.',
        ]);

    expect(PalmReadingBooking::count())->toBe(0);
    Mail::assertNothingQueued();
});

test('a booking rejects invalid values', function (array $overrides, string $field, string $message) {
    $this->post(route('palm-readings.store'), palmReadingPayload($overrides))
        ->assertSessionHasErrors([$field => $message]);

    expect(PalmReadingBooking::count())->toBe(0);
})->with([
    'phone with letters' => [['palm_phone' => '09abc45678'], 'palm_phone', 'Số điện thoại không hợp lệ.'],
    'phone too short' => [['palm_phone' => '091234'], 'palm_phone', 'Số điện thoại không hợp lệ.'],
    'impossible date' => [['palm_birth_day' => 31, 'palm_birth_month' => 2], 'palm_birth_date', 'Ngày sinh không hợp lệ.'],
]);

test('a booking rejects an unknown package', function () {
    $this->post(route('palm-readings.store'), palmReadingPayload(['palm_package' => 'vip']))
        ->assertSessionHasErrors('palm_package');
});

test('the home page form posts to the booking route', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('action="'.route('palm-readings.store').'"', false)
        ->assertSee('name="palm_phone"', false);
});

describe('admin', function () {
    beforeEach(function () {
        Filament::setCurrentPanel('admin');
    });

    test('admins see bookings in the admin panel', function () {
        PalmReadingBooking::factory()->create(['name' => 'Lê Văn Cường']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(PalmReadingBookingResource::getUrl('index'))
            ->assertOk()
            ->assertSeeText('Lê Văn Cường');
    });

    test('regular users cannot open the bookings screen', function () {
        $this->actingAs(User::factory()->create())
            ->get(PalmReadingBookingResource::getUrl('index'))
            ->assertForbidden();
    });

    test('an admin can mark a booking as contacted and undo it', function () {
        $this->actingAs(User::factory()->admin()->create());
        $booking = PalmReadingBooking::factory()->create();

        Livewire::test(ManagePalmReadingBookings::class)
            ->callTableAction('markContacted', $booking);

        expect($booking->refresh()->contacted_at)->not->toBeNull();

        Livewire::test(ManagePalmReadingBookings::class)
            ->callTableAction('markPending', $booking);

        expect($booking->refresh()->contacted_at)->toBeNull();
    });

    test('the pending filter hides contacted bookings', function () {
        $this->actingAs(User::factory()->admin()->create());
        $pending = PalmReadingBooking::factory()->create();
        $contacted = PalmReadingBooking::factory()->contacted()->create();

        Livewire::test(ManagePalmReadingBookings::class)
            ->filterTable('contacted', false)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$contacted]);
    });
});
