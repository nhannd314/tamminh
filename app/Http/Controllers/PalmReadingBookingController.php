<?php

namespace App\Http\Controllers;

use App\Gender;
use App\Http\Requests\StorePalmReadingBookingRequest;
use App\Mail\PalmReadingBooked;
use App\Models\PalmReadingBooking;
use App\Models\User;
use App\PalmReadingPackage;
use App\Support\ActivityFeed;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class PalmReadingBookingController extends Controller
{
    /**
     * Lưu lịch xem chỉ tay và gửi email báo cho mọi tài khoản admin.
     */
    public function __invoke(StorePalmReadingBookingRequest $request): RedirectResponse
    {
        $booking = PalmReadingBooking::create([
            'user_id' => $request->user('customer')?->id,
            'name' => $request->string('palm_name')->trim()->toString(),
            'gender' => $request->enum('palm_gender', Gender::class),
            'birth_date' => $request->birthDate(),
            'phone' => $request->string('palm_phone')->toString(),
            'package' => $request->enum('palm_package', PalmReadingPackage::class),
        ]);

        ActivityFeed::announce('palm', ActivityFeed::displayName($booking->name).' vừa đặt lịch xem chỉ tay');

        $adminEmails = User::query()->where('role', UserRole::Admin)->pluck('email');

        if ($adminEmails->isNotEmpty()) {
            Mail::to($adminEmails)->queue(new PalmReadingBooked($booking));
        }

        return redirect(route('home'))
            ->with('toast', 'Đã nhận yêu cầu đặt lịch - Tâm Minh sẽ liên hệ xác nhận sớm nhất.');
    }
}
