<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Nhận diện, cắt khuôn mặt và đo Tam đình bằng script Python (MediaPipe Face Mesh)
 * ở resources/python/face_landmarks.py.
 */
class FaceDetector
{
    /**
     * Đo khuôn mặt trong ảnh `$portraitPath` (disk local) và lưu ảnh cắt vào thư mục faces/.
     * Trả về null khi không thấy khuôn mặt.
     *
     * @return array{crop_path: string, box: list<float>, lines: list<float>, ratios: list<int>}|null
     *
     * @throws RuntimeException khi không chạy được script (thiếu Python, thiếu thư viện…).
     */
    public function measure(string $portraitPath): ?array
    {
        $disk = Storage::disk('local');
        $cropPath = 'faces/'.Str::uuid().'.jpg';
        $disk->makeDirectory('faces');

        $result = Process::timeout(config('services.face_detector.timeout'))->env($this->environment())->run([
            config('services.face_detector.python'),
            config('services.face_detector.script'),
            $disk->path($portraitPath),
            $disk->path($cropPath),
        ]);

        // MediaPipe ghi log ra stderr; kết quả là dòng JSON cuối cùng của stdout.
        $output = json_decode((string) Str::of($result->output())->trim()->explode("\n")->last(), true);

        if ($result->failed() || ! is_array($output)) {
            throw new RuntimeException('Không chạy được nhận diện khuôn mặt: '.Str::substr($result->errorOutput() ?: $result->output(), -1500));
        }

        if (! ($output['found'] ?? false)) {
            return null;
        }

        return [
            'crop_path' => $cropPath,
            'box' => $output['box'],
            'lines' => $output['lines'],
            'ratios' => $output['ratios'],
        ];
    }

    /**
     * `php artisan serve` và dịch vụ Apache chỉ truyền một phần biến môi trường cho tiến trình con:
     * - Windows: Python không khởi động được nếu thiếu SYSTEMROOT.
     * - matplotlib (MediaPipe import) cần thư mục ghi cache, mặc định nằm trong thư mục home.
     *
     * @return array<string, string>
     */
    private function environment(): array
    {
        $cacheDirectory = storage_path('framework/cache/matplotlib');
        File::ensureDirectoryExists($cacheDirectory);

        $environment = [
            'MPLCONFIGDIR' => $cacheDirectory,
            'PYTHONIOENCODING' => 'utf-8',
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $environment['SYSTEMROOT'] = getenv('SYSTEMROOT') ?: ($_SERVER['SystemRoot'] ?? 'C:\\Windows');
        }

        return $environment;
    }
}
