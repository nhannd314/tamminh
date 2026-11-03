<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPortraitRequest;
use App\Support\FaceDetector;
use App\Support\ProcessedPortrait;
use Illuminate\Http\JsonResponse;

/**
 * Nhận ảnh ngay khi khách chọn ảnh trên form xem tướng: lưu ảnh, cắt khuôn mặt và đo Tam đình
 * trong lúc khách còn điền các ô khác. Form chỉ cần gửi kèm `portrait_upload` = id trả về.
 */
class PortraitUploadController extends Controller
{
    public function __invoke(UploadPortraitRequest $request, FaceDetector $detector): JsonResponse
    {
        $portrait = ProcessedPortrait::process($request->file('portrait'), $detector);
        $portrait->remember();

        return response()->json([
            'id' => $portrait->id,
            'face_detected' => $portrait->faceMetrics !== null,
        ]);
    }
}
