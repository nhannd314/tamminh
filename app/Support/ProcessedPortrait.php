<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Ảnh chân dung đã lưu và đo khuôn mặt (cắt mặt, kẻ Tam đình).
 *
 * Form xem tướng gửi ảnh lên ngay khi khách chọn ảnh; kết quả được giữ trong session theo `id`
 * để lúc bấm "Xem kết quả" chỉ cần gửi id, không phải tải và đo lại.
 */
final readonly class ProcessedPortrait
{
    private const SESSION_KEY = 'portrait_upload';

    /**
     * @param  array{box: list<float>, lines: list<float>, ratios: list<int>, features?: array<string, list<float>>}|null  $faceMetrics
     */
    public function __construct(
        public string $id,
        public string $portraitPath,
        public ?string $faceCropPath,
        public ?array $faceMetrics,
    ) {}

    /**
     * @throws ValidationException khi không thấy khuôn mặt trong ảnh.
     */
    public static function process(UploadedFile $file, FaceDetector $detector): self
    {
        $portraitPath = $file->store('portraits', 'local');

        try {
            $face = $detector->measure($portraitPath);
        } catch (RuntimeException $exception) {
            // Máy chủ chưa cài Python/MediaPipe thì vẫn nhận ảnh, chỉ thiếu số đo khuôn mặt.
            report($exception);
            $face = false;
        }

        if ($face === null) {
            Storage::disk('local')->delete($portraitPath);

            throw ValidationException::withMessages([
                'portrait' => 'Không nhận diện được khuôn mặt trong ảnh. Vui lòng dùng ảnh chụp chính diện, rõ mặt.',
            ]);
        }

        return new self(
            id: (string) Str::uuid(),
            portraitPath: $portraitPath,
            faceCropPath: $face['crop_path'] ?? null,
            faceMetrics: $face ? Arr::except($face, 'crop_path') : null,
        );
    }

    /**
     * Ảnh đã xử lý trước trong session của khách, null khi không có hoặc không khớp id.
     */
    public static function recall(?string $id): ?self
    {
        $stored = session(self::SESSION_KEY);

        return $id !== null && ($stored['id'] ?? null) === $id ? new self(...$stored) : null;
    }

    /**
     * Lưu vào session; ảnh đã chọn trước đó (khách đổi ảnh) bị xoá.
     */
    public function remember(): void
    {
        $previous = session(self::SESSION_KEY);

        if ($previous !== null && $previous['id'] !== $this->id) {
            (new self(...$previous))->discard();
        }

        session([self::SESSION_KEY => get_object_vars($this)]);
    }

    /**
     * Đã dùng cho một lượt xem: bỏ khỏi session, giữ lại file.
     */
    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function discard(): void
    {
        Storage::disk('local')->delete(array_filter([$this->portraitPath, $this->faceCropPath]));
    }

    /**
     * @return array{portrait_path: string, face_crop_path: string|null, face_metrics: array<string, mixed>|null}
     */
    public function attributes(): array
    {
        return [
            'portrait_path' => $this->portraitPath,
            'face_crop_path' => $this->faceCropPath,
            'face_metrics' => $this->faceMetrics,
        ];
    }
}
