<?php

namespace App;

enum AnalysisStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Đang chờ',
            self::Processing => 'Đang phân tích',
            self::Completed => 'Hoàn tất',
            self::Failed => 'Thất bại',
        };
    }
}
