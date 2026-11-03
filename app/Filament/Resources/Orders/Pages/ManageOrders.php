<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * Đơn do khách tự đặt ở trang thanh toán nên không có nút tạo mới.
 */
class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;
}
