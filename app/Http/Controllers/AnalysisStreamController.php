<?php

namespace App\Http\Controllers;

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Support\AnalysisGenerator;
use App\Support\AnalysisSections;
use App\Support\Astrology\BirthChart;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Luồng SSE cho trang kết quả: gọi Gemini (stream) cho các phần chưa có nội dung và đẩy HTML
 * của phần đang viết về trình duyệt sau mỗi đợt chữ, để khách đọc được ngay thay vì chờ cả bài.
 *
 * Sự kiện: `section` {id, html} thay phần tử có id đó; `failed` {message}; `busy` (đang có tiến trình khác viết);
 * `done` {html} là thẻ "Tổng quan vận mệnh" ở thanh bên.
 */
class AnalysisStreamController extends Controller
{
    /**
     * Gửi bản xem trước tối đa mỗi khoảng này (giây) để không render Blade quá dày.
     */
    private const PREVIEW_INTERVAL = 0.3;

    public function __invoke(FaceAnalysis $analysis, AnalysisGenerator $generator): StreamedResponse
    {
        return response()->stream(function () use ($analysis, $generator): void {
            // Khách đóng trang giữa chừng thì vẫn viết nốt và lưu lại.
            ignore_user_abort(true);
            set_time_limit(0);

            $lock = $analysis->generationLock();

            if (! $lock->get()) {
                $this->send('busy');

                return;
            }

            try {
                $this->generate($analysis->load('sections'), $generator);
            } finally {
                $lock->release();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function generate(FaceAnalysis $analysis, AnalysisGenerator $generator): void
    {
        $chart = BirthChart::fromDate($analysis->birth_date, $analysis->birth_hour, $analysis->birth_minute);

        foreach ($analysis->pendingSections() as $section) {
            $sentAt = 0.0;

            try {
                $content = $generator->generate($analysis, $section, function (array $preview) use ($analysis, $chart, $section, &$sentAt): void {
                    if (microtime(true) - $sentAt >= self::PREVIEW_INTERVAL) {
                        $sentAt = microtime(true);
                        $this->sendSection($analysis, $chart, $section, $preview);
                    }
                });
            } catch (Throwable $exception) {
                report($exception);
                $analysis->markGenerationFailed($section, $exception);
                $this->send('failed', [
                    'message' => 'Chưa luận giải được phần này, vui lòng liên hệ Tâm Minh để được hỗ trợ.',
                    'html' => $analysis->section(AnalysisSection::Overview)?->isGenerated() ? $this->scoreCard($analysis) : null,
                ]);

                return;
            }

            $analysis->saveGeneratedContent($section, $content, $generator->model());
            $this->sendSection($analysis, $chart, $section);
        }

        $this->send('done', ['html' => $this->scoreCard($analysis)]);
    }

    private function scoreCard(FaceAnalysis $analysis): string
    {
        return view('pages.analysis.score-card', ['sections' => AnalysisSections::for($analysis)])->render();
    }

    /**
     * @param  array<string, mixed>|null  $preview  Nội dung đang viết dở; null là bản đã lưu.
     */
    private function sendSection(FaceAnalysis $analysis, BirthChart $chart, AnalysisSection $section, ?array $preview = null): void
    {
        $sections = AnalysisSections::for($analysis, $preview === null ? [] : [$section->value => $preview]);

        // Phần Tổng quan chỉ thay đoạn văn, để hiệu ứng của bát tự và sơ đồ ngũ hành chạy trọn.
        [$id, $html] = $section === AnalysisSection::Overview
            ? ["{$section->anchor()}-destiny", view('pages.analysis.overview-destiny', ['chart' => $chart, 'sections' => $sections])->render()]
            : [$section->anchor(), view("pages.analysis.sections.{$section->anchor()}", ['analysis' => $analysis, 'item' => $sections[$section->value], 'sections' => $sections])->render()];

        $this->send('section', ['id' => $id, 'html' => $html]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function send(string $event, array $data = []): void
    {
        echo "event: {$event}\ndata: ".json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
