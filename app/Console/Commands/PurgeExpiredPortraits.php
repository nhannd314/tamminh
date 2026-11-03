<?php

namespace App\Console\Commands;

use App\AnalysisOrderStatus;
use App\Models\FaceAnalysis;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Xóa ảnh chân dung (ảnh gốc và ảnh cắt) đã lưu quá FaceAnalysis::PORTRAIT_RETENTION_DAYS ngày.
 * Kết quả luận giải và số đo khuôn mặt vẫn giữ; chỉ các phần cần ảnh là không mua thêm được nữa.
 *
 * Chưa xóa khi lượt xem còn phần đã thanh toán nhưng chưa viết xong, hoặc có đơn vừa tạo trong 24 giờ
 * (khách có thể đang chuyển khoản). Ảnh tải lên nhưng không gửi form (không thuộc lượt xem nào) cũng bị xóa.
 */
#[Signature('analyses:purge-portraits')]
#[Description('Xóa ảnh chân dung đã lưu quá '.FaceAnalysis::PORTRAIT_RETENTION_DAYS.' ngày')]
class PurgeExpiredPortraits extends Command
{
    private const DIRECTORIES = ['portraits', 'faces'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subDays(FaceAnalysis::PORTRAIT_RETENTION_DAYS);
        $purged = 0;

        FaceAnalysis::query()
            ->whereNotNull('portrait_path')
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave('orders', fn (Builder $query) => $query
                ->where('status', AnalysisOrderStatus::Pending)
                ->where('created_at', '>', now()->subDay()))
            ->whereDoesntHave('sections', fn (Builder $query) => $query
                ->whereNotNull('unlocked_at')
                ->whereNull('generated_at'))
            ->lazyById()
            ->each(function (FaceAnalysis $analysis) use (&$purged): void {
                $analysis->deletePortrait();
                $purged++;
            });

        $orphans = $this->deleteOrphanFiles($cutoff->getTimestamp());

        $this->components->info("Đã xóa ảnh của {$purged} lượt xem và {$orphans} tệp ảnh không còn dùng.");

        return self::SUCCESS;
    }

    /**
     * Tệp ảnh cũ hơn mốc lưu trữ mà không lượt xem nào trỏ tới (ảnh đã chọn nhưng bỏ dở form).
     */
    private function deleteOrphanFiles(int $cutoff): int
    {
        $disk = Storage::disk('local');
        $deleted = 0;

        foreach (self::DIRECTORIES as $directory) {
            foreach ($disk->files($directory) as $path) {
                if ($disk->lastModified($path) > $cutoff) {
                    continue;
                }

                $isReferenced = FaceAnalysis::query()
                    ->where(fn (Builder $query) => $query->where('portrait_path', $path)->orWhere('face_crop_path', $path))
                    ->exists();

                if (! $isReferenced) {
                    $disk->delete($path);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
