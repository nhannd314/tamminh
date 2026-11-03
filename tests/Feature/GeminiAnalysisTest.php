<?php

use App\AnalysisSection;
use App\AnalysisStatus;
use App\Jobs\GenerateAnalysisSection;
use App\Models\FaceAnalysis;
use App\Models\Setting;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

const GEMINI_URL = 'generativelanguage.googleapis.com/v1beta/models/gemini-test:streamGenerateContent*';

beforeEach(function () {
    config(['services.gemini.key' => 'gemini-key', 'services.gemini.model' => 'gemini-test']);
});

/**
 * Luồng SSE như streamGenerateContent: một đợt suy luận rồi JSON trả lời bị cắt làm hai đợt.
 *
 * @param  array<string, mixed>  $content
 */
function geminiReply(array $content): PromiseInterface
{
    $json = json_encode($content);
    $half = intdiv(strlen($json), 2);
    $event = fn (array $parts, ?string $finishReason = null): string => 'data: '.json_encode(['candidates' => [array_filter([
        'content' => ['parts' => $parts],
        'finishReason' => $finishReason,
    ])]])."\r\n\r\n";

    return Http::response(
        $event([['text' => 'suy luận…', 'thought' => true]]).$event([['text' => substr($json, 0, $half)]]).$event([['text' => substr($json, $half)]], 'STOP'),
        200,
        ['Content-Type' => 'text/event-stream'],
    );
}

/**
 * @return array<string, mixed>
 */
function overviewReply(): array
{
    return [
        'score' => 82,
        'verdict' => 'Vận khí rất tốt',
        'ratios' => ['good' => 60, 'neutral' => 30, 'caution' => 20],
        'destiny' => ['Đoạn bản mệnh do Gemini viết.', 'Đoạn cấu trúc bát tự.'],
    ];
}

/**
 * @return array<string, mixed>
 */
function nguHanhReply(): array
{
    return [
        'tags' => ['Thủy vượng', 'Mộc tương sinh'],
        'radar' => ['hoa' => 10, 'tho' => 10, 'kim' => 20, 'thuy' => 40, 'moc' => 20],
        'summary' => 'Khuôn mặt tròn đầy, hành Thủy chủ đạo.',
        'strengths' => ['Linh hoạt', 'Giao tiếp tốt', 'Nhạy bén'],
        'cautions' => ['Dễ thay đổi ý định'],
        'blocks' => [['title' => 'Hình dáng khuôn mặt: mặt hình Thủy', 'items' => [
            ['label' => '', 'text' => 'Câu dẫn.'],
            ['label' => 'Trán', 'text' => 'Trán rộng, sáng.'],
        ]]],
        'balance' => [
            ['element' => 'moc', 'title' => 'Tăng Mộc', 'hint' => 'Màu xanh lá'],
            ['element' => 'kim', 'title' => 'Giữ Kim', 'hint' => 'Trang sức bạc'],
            ['element' => 'tho', 'title' => 'Giảm Thổ', 'hint' => 'Tránh màu vàng đất'],
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function thanKhiSacReply(): array
{
    return [
        'tags' => ['Thần thái tốt', 'Khí sắc ổn định'],
        'summary' => 'Ánh mắt sáng, sắc da đều.',
        'rating' => 4,
        'blocks' => [
            ['title' => '1. Về Thần (Tinh thần, Ý chí và Thần thái)', 'items' => [
                ['label' => 'Biểu hiện', 'text' => 'Ánh mắt sáng.'],
                ['label' => 'Luận giải', 'text' => 'Tư duy tỉnh táo.'],
            ]],
            ['title' => 'Tổng kết', 'items' => [['label' => '', 'text' => 'Thần thái và sắc diện hài hòa.']]],
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function tamDinhReply(): array
{
    $zone = fn (string $text, string $conclusion): array => [
        'blocks' => [['title' => '1. Bộ vị', 'items' => [['label' => 'Đặc điểm', 'text' => $text]]]],
        'conclusion' => $conclusion,
    ];

    return [
        'tags' => ['Trung đình tốt', 'Hậu vận ổn'],
        'ratios' => [24, 32, 24],
        'summary' => ['Tam đình khá cân đối.', 'Trung đình dài nhất (40%).'],
        'rating' => 4,
        'zones' => [
            'thuong' => $zone('Trán cao, rộng.', 'Thượng đình sáng.'),
            'trung' => $zone('Sống mũi thẳng.', ''),
            'ha' => $zone('Cằm đầy đặn.', 'Hậu vận ổn định.'),
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function nguQuanReply(): array
{
    $zone = fn (string $text): array => [
        'blocks' => [['title' => '1. Hình dáng', 'items' => [['label' => 'Đặc điểm', 'text' => $text]]]],
        'conclusion' => "Tổng kết: {$text}",
    ];

    return [
        'tags' => ['Mắt có thần', 'Mũi giữ tài'],
        'summary' => ['Ngũ quan cân đối.', 'Mắt và mũi nổi bật nhất.'],
        'zones' => [
            'long_may' => $zone('Mày thanh gọn.'),
            'mat' => $zone('Mắt sáng.'),
            'tai' => $zone('Tai bị tóc che, không quan sát rõ.'),
            'mui' => $zone('Sống mũi thẳng.'),
            'mieng' => $zone('Môi cân xứng.'),
        ],
    ];
}

/**
 * Trả lời Gemini đưa các cung theo thứ tự khác với thứ tự hiển thị.
 *
 * @return array<string, mixed>
 */
function thapNhiCungReply(): array
{
    $palace = ['verdict' => 'Cát', 'text' => 'Cung sáng sủa.', 'analysis' => 'Vùng này sáng.', 'advice' => 'Giữ nếp sống điều độ.'];
    $others = ['menh', 'tai_bach', 'huynh_de', 'dien_trach', 'tu_tuc', 'no_boc', 'phu_the', 'tat_ach', 'thien_di', 'quan_loc', 'phuc_duc'];

    return [
        'tags' => ['Quan lộc sáng', 'Tài bạch vững'],
        'summary' => 'Mệnh cung và Quan lộc cung sáng, Phụ mẫu cung cần lưu tâm.',
        'palaces' => [
            'phu_mau' => ['verdict' => 'Cần lưu ý', 'text' => 'Nên quan tâm cha mẹ.', 'analysis' => 'Hai góc trán không đều.', 'advice' => 'Thăm hỏi cha mẹ thường xuyên.'],
            ...array_fill_keys($others, $palace),
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function phongThuyReply(): array
{
    return [
        'tags' => ['Hợp hướng Tây', 'Dùng Thổ sinh Kim'],
        'summary' => 'Nên dùng Thổ để sinh Kim, hạn chế Hỏa.',
        'blocks' => [
            'mau_sac' => ['ban_menh' => 'Trắng, bạc', 'tuong_sinh' => 'Vàng, nâu đất (Thổ sinh Kim)', 'nen_tranh' => 'Đỏ, hồng (Hỏa khắc Kim)'],
            'huong' => ['huong_nha' => 'Tây, Tây Bắc', 'ban_lam_viec' => 'Quay mặt về Tây Bắc', 'huong_ngu' => 'Đầu giường hướng Tây', 'nam_nay' => 'Đặt bàn làm việc ở Tây Bắc'],
            'vat_pham' => ['da_quy' => 'Thạch anh trắng', 'linh_vat' => 'Tỳ hưu', 'trang_suc' => 'Nhẫn bạc', 'dat_tai_nha' => 'Chuông gió kim loại'],
            'tuoi_hop' => ['tam_hop' => 'Tý, Thìn', 'luc_hop' => 'Tỵ', 'nen_tranh' => 'Dần, Hợi', 'hop_menh' => 'Mệnh Thổ, mệnh Thủy'],
            'so_ngay' => ['so_may_man' => '6, 7', 'ngay_tot' => 'Ngày Tý, Thìn', 'gio_hoang_dao' => 'Giờ Thìn (7–9h)', 'khoi_su' => 'Mùa thu'],
            'hoa_giai' => ['bep' => 'Không đặt bếp ở Tây Bắc', 'goc_nhon' => 'Che bằng cây xanh', 'cua' => 'Treo rèm', 'guong' => 'Không đặt đối diện giường'],
        ],
    ];
}

/**
 * @return array<string, mixed>
 */
function adviceReply(): array
{
    return [
        'tags' => ['Tu tâm dưỡng tính', 'Chọn thời khởi sự'],
        'summary' => 'Phát huy sự quyết đoán, bớt nóng vội.',
        'blocks' => [
            'tam_phap' => ['tu_tam' => 'Bớt nóng vội, lắng nghe nhiều hơn', 'thien_dinh' => '10 phút mỗi sáng', 'hanh_thien' => 'Quan tâm cha mẹ'],
            'hanh_dong' => ['hien_tai' => '35-44 tuổi: tích lũy chuyên môn', 'muoi_nam_toi' => '45-54 tuổi: mở rộng đầu tư', 'nen_lam' => 'Giữ tài chính minh bạch', 'nen_tranh' => 'Bảo lãnh tài chính cho người khác'],
            'thoi_diem' => ['nam_tot' => '2032 Nhâm Tý', 'thang_tot' => 'Tháng 7, 8 âm lịch', 'ngay_gio' => 'Ngày Tý, giờ Thìn (7–9h)', 'than_trong' => '2034 Giáp Dần (lục xung)'],
            'vat_pham' => ['linh_vat' => 'Tỳ hưu nhỏ', 'deo_ben_nguoi' => 'Vòng thạch anh', 'huong_nha' => 'Tây, Tây Bắc', 'bo_tri' => 'Phòng khách sáng'],
        ],
    ];
}

test('the overview is written from the birth chart and saved', function () {
    Http::fake([GEMINI_URL => geminiReply(overviewReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['birth_date' => '1992-08-15', 'birth_hour' => 21, 'birth_minute' => 30]);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::Overview);

    $section = $analysis->refresh()->section(AnalysisSection::Overview);

    expect($section->isGenerated())->toBeTrue()
        ->and($section->content['destiny'][0])->toBe('Đoạn bản mệnh do Gemini viết.')
        ->and($section->content['ratios'])->toBe(['good' => 55, 'neutral' => 27, 'caution' => 18])
        ->and($analysis->status)->toBe(AnalysisStatus::Processing)
        ->and($analysis->ai_model)->toBe('gemini-test');

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('x-goog-api-key', 'gemini-key')
        && str_contains($request['contents'][0]['parts'][0]['text'], 'Kiếm Phong Kim')
        && str_contains($request['contents'][0]['parts'][0]['text'], 'Tháng: Mậu Thân')
        && str_contains($request['system_instruction']['parts'][0]['text'], '"quý chủ"')
        && $request['generationConfig']['responseMimeType'] === 'application/json');
});

test('ngu hanh is read from the face photo and completes the analysis', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => geminiReply(nguHanhReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);
    $analysis->sections()->create(['type' => AnalysisSection::Overview, 'content' => overviewReply(), 'generated_at' => now()]);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::NguHanh);

    $content = $analysis->refresh()->section(AnalysisSection::NguHanh)->content;

    expect($content['blocks'][0]['items'])->toBe([['text' => 'Câu dẫn.'], ['label' => 'Trán', 'text' => 'Trán rộng, sáng.']])
        ->and($content['radar']['thuy'])->toBe(40)
        ->and($analysis->status)->toBe(AnalysisStatus::Completed);

    Http::assertSent(fn (Request $request): bool => $request['contents'][0]['parts'][1]['inline_data']['data'] === base64_encode('face-bytes'));
});

test('a malformed reply fails the analysis without saving content', function () {
    Http::fake([GEMINI_URL => geminiReply(['score' => 'cao'])]);
    $analysis = FaceAnalysis::factory()->processing()->create();

    expect(fn () => GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::Overview))
        ->toThrow(RuntimeException::class, 'sai cấu trúc');

    expect($analysis->refresh()->status)->toBe(AnalysisStatus::Failed)
        ->and($analysis->failure_reason)->toContain('[overview]')
        ->and($analysis->section(AnalysisSection::Overview))->toBeNull();
});

test('a gemini error message is kept as the failure reason', function () {
    Http::fake([GEMINI_URL => Http::response(['error' => ['code' => 402, 'message' => 'Your prepayment credits are depleted.']], 402)]);
    $analysis = FaceAnalysis::factory()->processing()->create();

    expect(fn () => GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::Overview))->toThrow(RuntimeException::class);

    expect($analysis->refresh()->failure_reason)->toBe('[overview] Gemini lỗi 402 (model gemini-test): Your prepayment credits are depleted.');
    Http::assertSentCount(1);
});

test('generated content replaces the sample on the result page', function () {
    $analysis = FaceAnalysis::factory()->completed()->create();
    $analysis->sections()->create(['type' => AnalysisSection::Overview, 'content' => overviewReply(), 'generated_at' => now()]);
    $analysis->sections()->create(['type' => AnalysisSection::NguHanh, 'content' => nguHanhReply(), 'generated_at' => now()]);

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Đoạn bản mệnh do Gemini viết.')
        ->assertSeeText('Vận khí rất tốt')
        ->assertSeeText('Khuôn mặt tròn đầy, hành Thủy chủ đạo.')
        ->assertDontSeeText('Ý chí kiên cường')
        ->assertDontSee('data-generation-status', false);
});

test('sections waiting for gemini show a spinner instead of sample content', function () {
    $analysis = FaceAnalysis::factory()->processing()->create();

    $this->get(route('analyses.show', $analysis))
        ->assertSee('data-generation-stream="'.route('analyses.stream', $analysis).'"', false)
        ->assertSee('data-generation-loading', false)
        ->assertSeeText('Đang luận giải bản mệnh…')
        ->assertSeeText('Đang chấm điểm vận khí…')
        ->assertDontSeeText('Ý chí kiên cường')
        ->assertDontSeeText('Vận khí khá tốt');

    $this->getJson(route('analyses.generation-status', $analysis))->assertExactJson(['generating' => true]);
});

test('the result page streams each section as gemini writes it and saves the result', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => Http::sequence()->pushResponse(geminiReply(overviewReply()))->pushResponse(geminiReply(nguHanhReply()))]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);

    $stream = $this->get(route('analyses.stream', $analysis))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8')
        ->streamedContent();

    expect($stream)->toContain('event: section')
        ->toContain('"id":"overview-destiny"')
        ->toContain('"id":"ngu-hanh"')
        ->toContain('Đoạn bản mệnh do Gemini viết.')
        ->toContain('event: done')
        ->and($analysis->refresh()->status)->toBe(AnalysisStatus::Completed)
        ->and($analysis->pendingSections())->toBe([]);
});

test('paid face reading sections are streamed after the free sections', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => Http::sequence()
        ->pushResponse(geminiReply(overviewReply()))
        ->pushResponse(geminiReply(nguHanhReply()))
        ->pushResponse(geminiReply(thanKhiSacReply()))
        ->pushResponse(geminiReply(tamDinhReply()))
        ->pushResponse(geminiReply(nguQuanReply()))
        ->pushResponse(geminiReply(thapNhiCungReply()))
        ->pushResponse(geminiReply(phongThuyReply()))
        ->pushResponse(geminiReply(adviceReply()))]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);
    $analysis->unlock(AnalysisSection::ThanKhiSac, AnalysisSection::TamDinh, AnalysisSection::NguQuan, AnalysisSection::ThapNhiCung, AnalysisSection::PhongThuy, AnalysisSection::Advice);

    $stream = $this->get(route('analyses.stream', $analysis))->streamedContent();

    expect($stream)->toContain('"id":"than-khi-sac"')
        ->toContain('"id":"tam-dinh"')
        ->toContain('"id":"ngu-quan"')
        ->toContain('"id":"thap-nhi-cung"')
        ->toContain('"id":"phong-thuy"')
        ->toContain('"id":"advice"')
        ->toContain('Ánh mắt sáng, sắc da đều.')
        ->toContain('event: done')
        ->and($analysis->refresh()->status)->toBe(AnalysisStatus::Completed);
});

test('the stream reports a failure instead of hanging', function () {
    Http::fake([GEMINI_URL => Http::response(['error' => ['message' => 'Quota exceeded.']], 403)]);
    $analysis = FaceAnalysis::factory()->processing()->create();

    $stream = $this->get(route('analyses.stream', $analysis))->streamedContent();

    expect($stream)->toContain('event: failed')
        ->not->toContain('event: done')
        ->and($analysis->refresh()->status)->toBe(AnalysisStatus::Failed);
});

test('only one process writes an analysis at a time', function () {
    Http::fake([GEMINI_URL => geminiReply(overviewReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create();
    $analysis->generationLock()->get();

    expect($this->get(route('analyses.stream', $analysis))->streamedContent())->toContain('event: busy');

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::Overview);

    Http::assertNothingSent();
});

test('paying for a section queues its generation as a fallback for the page stream', function () {
    Setting::set('pricing.section.tam_dinh', 20000);
    $analysis = FaceAnalysis::factory()->completed()->create();
    Queue::fake([GenerateAnalysisSection::class]);

    $analysis->createOrder(AnalysisSection::TamDinh)->markAsPaid();
    Queue::assertPushed(GenerateAnalysisSection::class, fn (GenerateAnalysisSection $job): bool => $job->section === AnalysisSection::TamDinh
        && $job->delay > now());

    expect($analysis->refresh()->status)->toBe(AnalysisStatus::Processing);

    $this->get(route('analyses.show', $analysis))
        ->assertSee('data-generation-stream', false)
        ->assertSeeText('Đang đo tam đình…');
});

test('paying for the full package queues every section that has no content yet', function () {
    foreach (AnalysisSection::paid() as $section) {
        Setting::set("pricing.section.{$section->value}", 20000);
    }

    $analysis = FaceAnalysis::factory()->completed()->create();
    $analysis->unlock(AnalysisSection::TamDinh);
    $analysis->saveGeneratedContent(AnalysisSection::TamDinh, tamDinhReply(), 'gemini-test');
    Queue::fake([GenerateAnalysisSection::class]);

    $analysis->createOrder()->markAsPaid();

    $queued = Queue::pushed(GenerateAnalysisSection::class)->map(fn (GenerateAnalysisSection $job): AnalysisSection => $job->section)->all();

    expect($queued)->toEqualCanonicalizing(array_values(array_filter(AnalysisSection::paid(), fn (AnalysisSection $section): bool => $section !== AnalysisSection::TamDinh)))
        ->and($queued)->toContain(AnalysisSection::Advice);
});

test('than khi sac is read from the face photo', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => geminiReply(thanKhiSacReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::ThanKhiSac);

    $content = $analysis->refresh()->section(AnalysisSection::ThanKhiSac)->content;

    expect($content['rating'])->toBe(4)
        ->and($content['blocks'][1]['items'])->toBe([['text' => 'Thần thái và sắc diện hài hòa.']]);

    Http::assertSent(fn (Request $request): bool => str_contains($request['contents'][0]['parts'][0]['text'], 'THẦN - KHÍ - SẮC')
        && $request['contents'][0]['parts'][1]['inline_data']['data'] === base64_encode('face-bytes'));
});

test('tam dinh keeps the measured ratios and is split into three tabs', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => geminiReply(tamDinhReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create([
        'face_crop_path' => 'faces/a.jpg',
        'face_metrics' => ['box' => [20, 10, 60, 60], 'lines' => [20, 40, 62, 84], 'ratios' => [31, 36, 33]],
    ]);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::TamDinh);

    $content = $analysis->refresh()->section(AnalysisSection::TamDinh)->content;

    expect($content['ratios'])->toBe([31, 36, 33])
        ->and(array_column($content['tabs'], 'title'))->toBe(['Thượng đình', 'Trung đình', 'Hạ đình'])
        ->and($content['tabs'][0]['conclusion'])->toBe(['title' => 'Tổng kết về Thượng đình', 'text' => 'Thượng đình sáng.'])
        ->and($content['tabs'][1])->not->toHaveKey('conclusion');

    Http::assertSent(fn (Request $request): bool => str_contains($request['contents'][0]['parts'][0]['text'], 'dùng đúng số đo từ ảnh [31, 36, 33]'));

    $analysis->unlock(AnalysisSection::TamDinh);

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Trán cao, rộng.')
        ->assertSeeText('Tổng kết về Thượng đình');
});

test('ngu quan is read from the face photo and split into the five senses', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => geminiReply(nguQuanReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);
    $analysis->unlock(AnalysisSection::NguQuan);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::NguQuan);

    $content = $analysis->refresh()->section(AnalysisSection::NguQuan)->content;

    expect(array_column($content['tabs'], 'title'))->toBe(['Lông mày', 'Mắt', 'Tai', 'Mũi', 'Miệng'])
        ->and(array_column($content['tabs'], 'subtitle'))->toBe(['Bảo thọ quan', 'Giám sát quan', 'Thái thính quan', 'Thẩm biện quan', 'Xuất nạp quan'])
        ->and($content['tabs'][2]['conclusion']['title'])->toBe('Tổng kết về Tai');

    Http::assertSent(fn (Request $request): bool => str_contains($request['contents'][0]['parts'][0]['text'], 'NGŨ QUAN')
        && $request['contents'][0]['parts'][1]['inline_data']['data'] === base64_encode('face-bytes'));

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Mắt và mũi nổi bật nhất.')
        ->assertSeeText('Tai bị tóc che, không quan sát rõ.');
});

test('thap nhi cung lists the twelve palaces in display order with their positions', function () {
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    Http::fake([GEMINI_URL => geminiReply(thapNhiCungReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);
    $analysis->unlock(AnalysisSection::ThapNhiCung);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::ThapNhiCung);

    $palaces = $analysis->refresh()->section(AnalysisSection::ThapNhiCung)->content['palaces'];

    expect($palaces)->toHaveCount(12)
        ->and($palaces[0])->toMatchArray(['name' => 'Mệnh cung', 'position' => 'Ấn đường', 'verdict' => 'Cát'])
        ->and($palaces[11])->toMatchArray(['name' => 'Phụ mẫu cung', 'position' => 'Nhật giác - Nguyệt giác', 'verdict' => 'Cần lưu ý']);

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Phụ mẫu cung cần lưu tâm.')
        ->assertSeeText('Thăm hỏi cha mẹ thường xuyên.');
});

test('a palace verdict outside the three levels is rejected', function () {
    $reply = thapNhiCungReply();
    $reply['palaces']['menh']['verdict'] = 'Đại cát';
    Http::fake([GEMINI_URL => geminiReply($reply)]);
    Storage::fake('local');
    Storage::disk('local')->put('faces/a.jpg', 'face-bytes');
    $analysis = FaceAnalysis::factory()->processing()->create(['face_crop_path' => 'faces/a.jpg']);

    expect(fn () => GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::ThapNhiCung))
        ->toThrow(RuntimeException::class, 'sai cấu trúc');
});

test('phong thuy is given the age relations of the birth year and laid out in six groups', function () {
    $this->travelTo('2026-10-30');
    Http::fake([GEMINI_URL => geminiReply(phongThuyReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['birth_date' => '1992-08-15']);
    $analysis->unlock(AnalysisSection::PhongThuy);
    $analysis->saveGeneratedContent(AnalysisSection::NguHanh, nguHanhReply(), 'gemini-test');

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::PhongThuy);

    $blocks = $analysis->refresh()->section(AnalysisSection::PhongThuy)->content['blocks'];

    expect(array_column($blocks, 'title'))->toBe(['Màu sắc phù hợp', 'Hướng phong thủy', 'Đồ vật phong thủy', 'Tuổi hợp làm ăn', 'Số & ngày tốt', 'Hóa giải sát khí'])
        ->and($blocks[0]['items'][1])->toBe(['label' => 'Tương sinh', 'text' => 'Vàng, nâu đất (Thổ sinh Kim)'])
        ->and($blocks[1]['items'][3]['label'])->toBe('Năm 2026');

    Http::assertSent(function (Request $request): bool {
        $prompt = $request['contents'][0]['parts'][0]['text'];

        return str_contains($prompt, 'Quan hệ tuổi Thân')
            && str_contains($prompt, 'tam hợp Tý, Thìn;')
            && str_contains($prompt, 'lục hợp Tỵ; lục xung Dần; lục hại Hợi.')
            && str_contains($prompt, 'Tỷ lệ ngũ hành trên khuôn mặt: Hỏa 10%')
            && count($request['contents'][0]['parts']) === 1;
    });

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Nên dùng Thổ để sinh Kim, hạn chế Hỏa.')
        ->assertSeeText('Không đặt bếp ở Tây Bắc');
});

test('advice is given the coming years related to the birth year and the palaces needing care', function () {
    $this->travelTo('2026-10-30');
    Http::fake([GEMINI_URL => geminiReply(adviceReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['birth_date' => '1992-08-15']);
    $analysis->unlock(AnalysisSection::Advice, AnalysisSection::ThapNhiCung);
    $analysis->saveGeneratedContent(AnalysisSection::ThapNhiCung, [
        'tags' => [],
        'summary' => '',
        'palaces' => [
            ['name' => 'Mệnh cung', 'position' => 'Ấn đường', 'verdict' => 'Cát', 'text' => 'Ấn đường sáng.', 'analysis' => '', 'advice' => ''],
            ['name' => 'Phụ mẫu cung', 'position' => 'Nhật giác - Nguyệt giác', 'verdict' => 'Cần lưu ý', 'text' => 'Nên quan tâm cha mẹ.', 'analysis' => '', 'advice' => ''],
        ],
    ], 'gemini-test');

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::Advice);

    $blocks = $analysis->refresh()->section(AnalysisSection::Advice)->content['blocks'];

    expect(array_column($blocks, 'title'))->toBe(['Tâm pháp', 'Hành động', 'Thời điểm', 'Vật phẩm'])
        ->and($blocks[2]['items'][3])->toBe(['label' => 'Cần thận trọng', 'text' => '2034 Giáp Dần (lục xung)']);

    Http::assertSent(function (Request $request): bool {
        $prompt = $request['contents'][0]['parts'][0]['text'];

        return str_contains($prompt, '2028 Mậu Thân (hành Kim, năm tuổi (phạm Thái tuế))')
            && str_contains($prompt, '2031 Tân Hợi (hành Thủy, lục hại)')
            && str_contains($prompt, '2032 Nhâm Tý (hành Thủy, tam hợp)')
            && str_contains($prompt, '2034 Giáp Dần (hành Mộc, lục xung)')
            && str_contains($prompt, '2027 Đinh Mùi (hành Thổ)')
            && str_contains($prompt, 'giai đoạn 35-44 tuổi')
            && str_contains($prompt, 'Các cung cần lưu ý trên khuôn mặt: Phụ mẫu cung (Nên quan tâm cha mẹ.)')
            && ! str_contains($prompt, 'Mệnh cung (Ấn đường sáng.)');
    });

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Phát huy sự quyết đoán, bớt nóng vội.')
        ->assertSeeText('Bảo lãnh tài chính cho người khác');
});

test('tam dinh uses the estimated ratios when the photo was not measured', function () {
    Storage::fake('local');
    Storage::disk('local')->put('portraits/a.jpg', 'portrait-bytes');
    Http::fake([GEMINI_URL => geminiReply(tamDinhReply())]);
    $analysis = FaceAnalysis::factory()->processing()->create(['portrait_path' => 'portraits/a.jpg', 'face_crop_path' => null, 'face_metrics' => null]);

    GenerateAnalysisSection::dispatchSync($analysis, AnalysisSection::TamDinh);

    expect($analysis->refresh()->section(AnalysisSection::TamDinh)->content['ratios'])->toBe([30, 40, 30]);
});
