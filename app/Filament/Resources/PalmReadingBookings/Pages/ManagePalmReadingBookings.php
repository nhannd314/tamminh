<?php

namespace App\Filament\Resources\PalmReadingBookings\Pages;

use App\Filament\Resources\PalmReadingBookings\PalmReadingBookingResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * Lịch do khách tự gửi nên không có nút tạo mới.
 */
class ManagePalmReadingBookings extends ManageRecords
{
    protected static string $resource = PalmReadingBookingResource::class;
}
