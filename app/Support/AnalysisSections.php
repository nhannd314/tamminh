<?php

namespace App\Support;

use App\AnalysisSection;
use App\AnalysisStatus;
use App\Models\FaceAnalysis;
use Illuminate\Support\Collection;

/**
 * Dữ liệu từng phần cho trang kết quả: nội dung thật (đã mở khoá và đã sinh), nội dung đang stream, hoặc dữ liệu mẫu.
 *
 * `loading`: phần đã mở khoá đang chờ Gemini viết; view hiện spinner ở chỗ chưa có chữ thay vì dữ liệu mẫu.
 */
final class AnalysisSections
{
    /**
     * @param  array<string, array<string, mixed>>  $previews  Nội dung đang viết dở theo `AnalysisSection::value`.
     * @return Collection<string, array{type: AnalysisSection, unlocked: bool, loading: bool, isSample: bool, content: array<string, mixed>, price: int}>
     */
    public static function for(FaceAnalysis $analysis, array $previews = []): Collection
    {
        return collect(AnalysisSection::cases())->mapWithKeys(function (AnalysisSection $type) use ($analysis, $previews): array {
            $unlocked = $analysis->isUnlocked($type);
            $loading = $unlocked
                && $analysis->status !== AnalysisStatus::Failed
                && AnalysisGenerator::supports($type)
                && ! $analysis->section($type)?->isGenerated();

            // Phần còn khoá luôn dùng dữ liệu mẫu (hiển thị mờ) để nội dung thật không bao giờ lộ ra.
            $content = $unlocked ? ($previews[$type->value] ?? $analysis->section($type)?->content) : null;

            if ($content === null && $loading) {
                $content = AnalysisGenerator::preview($type, []);
            }

            return [$type->value => [
                'type' => $type,
                'unlocked' => $unlocked,
                'loading' => $loading,
                'isSample' => $content === null,
                'content' => $content ?? SampleAnalysis::for($type, $analysis->gender),
                'price' => $type->price(),
            ]];
        });
    }
}
