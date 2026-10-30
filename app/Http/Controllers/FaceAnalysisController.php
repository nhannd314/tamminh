<?php

namespace App\Http\Controllers;

use App\AnalysisSection;
use App\AnalysisStatus;
use App\Gender;
use App\Http\Requests\StoreFaceAnalysisRequest;
use App\Models\FaceAnalysis;
use App\OrderStatus;
use App\Support\Astrology\BirthChart;
use App\Support\FaceDetector;
use App\Support\PaymentInstructions;
use App\Support\SampleAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FaceAnalysisController extends Controller
{
    public function store(StoreFaceAnalysisRequest $request, FaceDetector $detector): RedirectResponse
    {
        $portraitPath = $request->file('portrait')->store('portraits', 'local');

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
            ])->redirectTo(route('home').'#xem-tuong');
        }

        $analysis = FaceAnalysis::create([
            'face_crop_path' => $face['crop_path'] ?? null,
            'face_metrics' => $face ? Arr::except($face, 'crop_path') : null,
            'user_id' => $request->user()?->id,
            'name' => $request->string('name')->squish()->toString(),
            'gender' => $request->enum('gender', Gender::class),
            'birth_date' => $request->birthDate(),
            'birth_hour' => $request->filled('birth_hour') ? $request->integer('birth_hour') : null,
            'birth_minute' => $request->filled('birth_hour') ? $request->integer('birth_minute') : null,
            'portrait_path' => $portraitPath,
            'status' => AnalysisStatus::Pending,
            'ip_address' => $request->ip(),
            'terms_accepted_at' => now(),
        ]);

        return to_route('analyses.show', $analysis);
    }

    public function show(FaceAnalysis $analysis): View
    {
        $analysis->load('sections');
        $chart = BirthChart::fromDate($analysis->birth_date, $analysis->birth_hour, $analysis->birth_minute);

        $sections = collect(AnalysisSection::cases())->mapWithKeys(function (AnalysisSection $type) use ($analysis): array {
            $unlocked = $analysis->isUnlocked($type);

            // Phần còn khoá luôn dùng dữ liệu mẫu (hiển thị mờ) để nội dung thật không bao giờ lộ ra.
            $content = $unlocked ? $analysis->section($type)?->content : null;

            return [$type->value => [
                'type' => $type,
                'unlocked' => $unlocked,
                'isSample' => $content === null,
                'content' => $content ?? SampleAnalysis::for($type, $analysis->gender),
                'price' => $type->price(),
            ]];
        });

        $pendingOrder = session('payment_order')
            ? $analysis->orders()->where('code', session('payment_order'))->where('status', OrderStatus::Pending)->first()
            : null;

        return view('pages.analysis.show', [
            'analysis' => $analysis,
            'chart' => $chart,
            'sections' => $sections,
            'fullPackage' => $analysis->fullPackageQuote(),
            'products' => SampleAnalysis::products(),
            'payment' => $pendingOrder ? PaymentInstructions::for($pendingOrder) : null,
        ]);
    }

    public function portrait(FaceAnalysis $analysis): StreamedResponse
    {
        return $this->image($analysis->portrait_path);
    }

    /**
     * Ảnh đã cắt sát khuôn mặt; chưa có thì trả ảnh gốc.
     */
    public function face(FaceAnalysis $analysis): StreamedResponse
    {
        return $this->image($analysis->face_crop_path ?? $analysis->portrait_path);
    }

    private function image(string $path): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Tạo (hoặc dùng lại) đơn chờ thanh toán cho một phần, hoặc trọn gói khi không gửi `section`,
     * rồi quay lại trang kết quả và mở cửa sổ chuyển khoản.
     */
    public function unlock(Request $request, FaceAnalysis $analysis): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['nullable', Rule::in(array_map(fn (AnalysisSection $type): string => $type->value, $analysis->lockedSections()))],
        ]);

        $section = AnalysisSection::tryFrom($validated['section'] ?? '');

        if ($section === null && $analysis->isFullyUnlocked()) {
            return to_route('analyses.show', $analysis);
        }

        $amount = $section?->price() ?? $analysis->fullPackageQuote()['amount'];

        // Bấm lại nút mở khoá thì dùng lại đơn cũ để người dùng không nhận hai mã chuyển khoản khác nhau.
        $order = $analysis->orders()
            ->where('status', OrderStatus::Pending)
            ->where('section', $section)
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subDay())
            ->latest('id')
            ->first() ?? $analysis->createOrder($section);

        return redirect(route('analyses.show', $analysis).'#'.($section?->anchor() ?? 'top'))
            ->with('payment_order', $order->code);
    }

    /**
     * Trạng thái đơn để cửa sổ thanh toán tự kiểm tra và tải lại trang khi SePay báo đã nhận tiền.
     */
    public function orderStatus(FaceAnalysis $analysis, string $code): JsonResponse
    {
        $order = $analysis->orders()->where('code', $code)->firstOrFail();

        return response()->json(['paid' => $order->isPaid()]);
    }
}
