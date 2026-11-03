<?php

namespace App\Filament\Resources\Affiliates\Pages;

use App\Filament\Resources\Affiliates\AffiliateResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * Khách tự tham gia affiliate trong trang tài khoản nên không có nút tạo mới.
 */
class ManageAffiliates extends ManageRecords
{
    protected static string $resource = AffiliateResource::class;
}
