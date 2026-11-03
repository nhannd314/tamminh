<?php

namespace App\Filament\Resources\AffiliateCommissions\Pages;

use App\Filament\Resources\AffiliateCommissions\AffiliateCommissionResource;
use Filament\Resources\Pages\ManageRecords;

/**
 * Hoa hồng tự sinh từ đơn giới thiệu nên không có nút tạo mới.
 */
class ManageAffiliateCommissions extends ManageRecords
{
    protected static string $resource = AffiliateCommissionResource::class;
}
