<?php

namespace App\Jobs;

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Support\AnalysisGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Throwable;

/**
 * Gọi Gemini sinh nội dung một phần luận giải rồi lưu vào FaceAnalysisSection::$content.
 * Trang kết quả thường đã stream xong trước; job chạy cho phần mở khoá qua webhook hoặc khi khách không mở trang.
 */
#[Tries(5)]
#[Backoff([30, 60, 120])]
#[Timeout(300)]
class GenerateAnalysisSection implements ShouldQueue
{
    use Queueable;

    public function __construct(public FaceAnalysis $analysis, public AnalysisSection $section) {}

    public function handle(AnalysisGenerator $generator): void
    {
        $lock = $this->analysis->generationLock();

        // Trang kết quả đang stream nội dung: chờ rồi kiểm tra lại.
        if (! $lock->get()) {
            $this->release(60);

            return;
        }

        try {
            $this->analysis->unsetRelation('sections');

            if ($this->analysis->section($this->section)?->isGenerated()) {
                return;
            }

            $content = $generator->generate($this->analysis, $this->section);
            $this->analysis->saveGeneratedContent($this->section, $content, $generator->model());
        } finally {
            $lock->release();
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->analysis->markGenerationFailed($this->section, $exception);
    }
}
