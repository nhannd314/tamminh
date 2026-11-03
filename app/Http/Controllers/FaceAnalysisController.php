<?php

namespace App\Http\Controllers;

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\AnalysisStatus;
use App\Gender;
use App\Http\Requests\StoreFaceAnalysisRequest;
use App\Models\FaceAnalysis;
use App\Support\ActivityFeed;
use App\Support\AffiliateReferral;
use App\Support\AnalysisSections;
use App\Support\Astrology\BirthChart;
use App\Support\FaceDetector;
use App\Support\PaymentInstructions;
use App\Support\ProcessedPortrait;
use App\Support\SampleAnalysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FaceAnalysisController extends Controller
{
    /**
     * Ảnh thường đã được gửi và đo trước (PortraitUploadController) nên bước này gần như tức thì.
     * Nội dung Gemini được stream ngay trên trang kết quả; job chỉ chạy dự phòng nếu khách không mở trang.
     */
    public function store(StoreFaceAnalysisRequest $request, FaceDetector $detector): RedirectResponse
    {
        if ($request->hasFile('portrait')) {
            try {
                $portrait = ProcessedPortrait::process($request->file('portrait'), $detector);
            } catch (ValidationException $exception) {
                throw $exception->redirectTo(route('home').'#xem-tuong');
            }
        } else {
            $portrait = $request->processedPortrait();
            $portrait->forget();
        }

        $analysis = FaceAnalysis::create([
            ...$portrait->attributes(),
            'user_id' => $request->user('customer')?->id,
            'affiliate_id' => AffiliateReferral::resolve($request)?->id,
            'name' => $request->string('name')->squish()->toString(),
            'gender' => $request->enum('gender', Gender::class),
            'birth_date' => $request->birthDate(),
            'birth_hour' => $request->filled('birth_hour') ? $request->integer('birth_hour') : null,
            'birth_minute' => $request->filled('birth_hour') ? $request->integer('birth_minute') : null,
            'status' => AnalysisStatus::Pending,
            'ip_address' => $request->ip(),
            'terms_accepted_at' => now(),
        ]);

        $analysis->generateContent(AnalysisSection::free(), now()->addMinutes(3));

        ActivityFeed::announce('analysis', ActivityFeed::displayName($analysis->name).' vừa đăng ký xem tướng luận giải');

        return to_route('analyses.show', $analysis);
    }

    public function show(FaceAnalysis $analysis): View
    {
        $analysis->load('sections');
        $chart = BirthChart::fromDate($analysis->birth_date, $analysis->birth_hour, $analysis->birth_minute);
        $sections = AnalysisSections::for($analysis);

        $pendingOrder = session('payment_order')
            ? $analysis->orders()->where('code', session('payment_order'))->where('status', AnalysisOrderStatus::Pending)->first()
            : null;

        $isFirstVisit = ! in_array($analysis->getKey(), session('viewed_analyses', []), true);

        if ($isFirstVisit) {
            session()->push('viewed_analyses', $analysis->getKey());
        }

        return view('pages.analysis.show', [
            'analysis' => $analysis,
            'chart' => $chart,
            'sections' => $sections,
            'fullPackage' => $analysis->fullPackageQuote(),
            'fullPackageDiscountPercent' => AnalysisSection::fullPackageDiscountPercent(),
            'hasBoughtSections' => count($analysis->lockedSections()) < count(AnalysisSection::paid()),
            'products' => SampleAnalysis::products(),
            'payment' => $pendingOrder ? PaymentInstructions::for($pendingOrder) : null,
            'playsIntro' => $isFirstVisit,
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

    private function image(?string $path): StreamedResponse
    {
        abort_unless($path !== null && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Tạo (hoặc dùng lại) đơn chờ thanh toán cho một phần, hoặc trọn gói khi không gửi `section`.
     * Gọi bằng fetch thì trả HTML cửa sổ chuyển khoản để mở ngay tại chỗ; không có JS thì tải lại trang kết quả.
     */
    public function unlock(Request $request, FaceAnalysis $analysis): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'section' => ['nullable', Rule::in(array_map(fn (AnalysisSection $type): string => $type->value, $analysis->purchasableSections()))],
        ]);

        $section = AnalysisSection::tryFrom($validated['section'] ?? '');

        if ($section === null && $analysis->purchasableSections() === []) {
            return $request->expectsJson() ? response()->json(['html' => null]) : to_route('analyses.show', $analysis);
        }

        // Khách xem tướng trước rồi mới bấm link giới thiệu vẫn được tính cho affiliate (nếu lượt xem chưa có ai giới thiệu).
        if ($analysis->affiliate_id === null && ($affiliate = AffiliateReferral::resolve($request)) !== null) {
            $analysis->update(['affiliate_id' => $affiliate->id]);
        }

        $amount = $section?->price() ?? $analysis->fullPackageQuote()['amount'];

        // Bấm lại nút mở khoá thì dùng lại đơn cũ để người dùng không nhận hai mã chuyển khoản khác nhau.
        $order = $analysis->orders()
            ->where('status', AnalysisOrderStatus::Pending)
            ->where('section', $section)
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subDay())
            ->latest('id')
            ->first() ?? $analysis->createOrder($section);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('pages.analysis.payment', ['analysis' => $analysis, 'payment' => PaymentInstructions::for($order)])->render(),
            ]);
        }

        return to_route('analyses.show', $analysis)->with('payment_order', $order->code);
    }

    /**
     * Trang kết quả hỏi định kỳ để tải lại khi Gemini sinh xong nội dung.
     */
    public function generationStatus(FaceAnalysis $analysis): JsonResponse
    {
        return response()->json(['generating' => $analysis->isGenerating()]);
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
