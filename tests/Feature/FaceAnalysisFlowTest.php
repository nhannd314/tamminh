<?php

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\AnalysisStatus;
use App\Jobs\GenerateAnalysisSection;
use App\Models\FaceAnalysis;
use App\Models\FaceAnalysisSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Queue::fake([GenerateAnalysisSection::class]);

    Process::fake([
        '*' => Process::result("I0000 mediapipe log\n".json_encode([
            'found' => true,
            'box' => [30.5, 10.5, 26.5, 27.1],
            'lines' => [13.2, 30.4, 57.6, 87.5],
            'ratios' => [23, 37, 40],
        ])),
    ]);
});

/**
 * @return array<string, mixed>
 */
function analysisPayload(array $overrides = []): array
{
    return [
        'name' => 'Nguyễn Duy Nhân',
        'gender' => 'male',
        'birth_day' => 15,
        'birth_month' => 8,
        'birth_year' => 1992,
        'birth_hour' => 21,
        'birth_minute' => 30,
        'portrait' => UploadedFile::fake()->image('portrait.jpg', 600, 800),
        'agree_terms' => '1',
        ...$overrides,
    ];
}

test('submitting the hero form stores the analysis and redirects to its result page', function () {
    $response = $this->post(route('analyses.store'), analysisPayload());

    $analysis = FaceAnalysis::sole();

    $response->assertRedirect(route('analyses.show', $analysis));

    expect($analysis->name)->toBe('Nguyễn Duy Nhân')
        ->and($analysis->birth_date->format('Y-m-d'))->toBe('1992-08-15')
        ->and($analysis->birth_time)->toBe('21:30')
        ->and($analysis->status)->toBe(AnalysisStatus::Processing)
        ->and($analysis->terms_accepted_at)->not->toBeNull();

    Queue::assertPushed(GenerateAnalysisSection::class, 2);
    Queue::assertPushed(GenerateAnalysisSection::class, fn (GenerateAnalysisSection $job): bool => $job->section === AnalysisSection::Overview);
    Queue::assertPushed(GenerateAnalysisSection::class, fn (GenerateAnalysisSection $job): bool => $job->section === AnalysisSection::NguHanh);

    Storage::disk('local')->assertExists($analysis->portrait_path);

    expect($analysis->face_crop_path)->toStartWith('faces/')
        ->and($analysis->face_metrics['ratios'])->toBe([23, 37, 40]);

    Process::assertRan(fn ($process) => str_contains(implode(' ', (array) $process->command), 'face_landmarks.py'));
});

test('an analysis belongs to the logged in customer but never to an admin session', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer, 'customer')->post(route('analyses.store'), analysisPayload());

    expect(FaceAnalysis::sole()->user_id)->toBe($customer->id);

    FaceAnalysis::query()->delete();
    $this->app['auth']->guard('customer')->logout();

    $this->actingAs(User::factory()->admin()->create(), 'web')->post(route('analyses.store'), analysisPayload());

    expect(FaceAnalysis::sole()->user_id)->toBeNull();
});

test('the photo is processed as soon as it is chosen and the form only sends its id', function () {
    $upload = $this->postJson(route('portraits.upload'), ['portrait' => UploadedFile::fake()->image('portrait.jpg', 600, 800)])
        ->assertOk()
        ->assertJson(['face_detected' => true]);

    $this->post(route('analyses.store'), analysisPayload(['portrait' => null, 'portrait_upload' => $upload->json('id')]))
        ->assertRedirect();

    $analysis = FaceAnalysis::sole();

    expect($analysis->face_metrics['ratios'])->toBe([23, 37, 40])
        ->and($analysis->face_crop_path)->toStartWith('faces/');
    Storage::disk('local')->assertExists($analysis->portrait_path);
    Process::assertRanTimes(fn ($process) => str_contains(implode(' ', (array) $process->command), 'face_landmarks.py'), 1);
});

test('choosing another photo discards the previous one', function () {
    $first = $this->postJson(route('portraits.upload'), ['portrait' => UploadedFile::fake()->image('a.jpg', 600, 800)]);
    $this->postJson(route('portraits.upload'), ['portrait' => UploadedFile::fake()->image('b.jpg', 600, 800)]);

    expect(Storage::disk('local')->allFiles('portraits'))->toHaveCount(1);

    $this->post(route('analyses.store'), analysisPayload(['portrait' => null, 'portrait_upload' => $first->json('id')]))
        ->assertSessionHasErrors(['portrait' => 'Ảnh đã hết hạn, vui lòng chọn lại ảnh chân dung.']);
});

test('a chosen photo without a face is rejected right away', function () {
    Process::fake(['*' => Process::result('{"found": false}')]);

    $this->postJson(route('portraits.upload'), ['portrait' => UploadedFile::fake()->image('portrait.jpg', 600, 800)])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('portrait');

    expect(Storage::disk('local')->allFiles('portraits'))->toBe([]);
});

test('a photo without a face is rejected', function () {
    Process::fake(['*' => Process::result('{"found": false}')]);

    $this->post(route('analyses.store'), analysisPayload())
        ->assertRedirect(route('home').'#xem-tuong')
        ->assertSessionHasErrors('portrait');

    expect(FaceAnalysis::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('portraits'))->toBe([]);
});

test('the analysis is still created when face detection is unavailable', function () {
    Process::fake(['*' => Process::result(errorOutput: 'No module named mediapipe', exitCode: 1)]);

    $this->post(route('analyses.store'), analysisPayload())->assertRedirect();

    expect(FaceAnalysis::sole()->face_metrics)->toBeNull();
});

test('measured tam dinh ratios are shown even while the section is locked', function () {
    $analysis = FaceAnalysis::factory()->create([
        'face_crop_path' => 'faces/a.jpg',
        'face_metrics' => ['box' => [0, 0, 100, 100], 'lines' => [10, 30, 60, 90], 'ratios' => [22, 41, 37]],
    ]);

    $this->get(route('analyses.show', $analysis))
        ->assertSee('style="top: 20%">22%</span>', false)
        ->assertSee('style="top: 45%">41%</span>', false)
        ->assertSee(route('analyses.face', $analysis), false);
});

test('birth time is optional', function () {
    $this->post(route('analyses.store'), analysisPayload(['birth_hour' => '', 'birth_minute' => '']))->assertRedirect();

    expect(FaceAnalysis::sole()->birth_hour)->toBeNull();
});

test('the form is validated and redirects back to the form anchor', function (array $overrides, string $field) {
    $this->from(route('home'))
        ->post(route('analyses.store'), analysisPayload($overrides))
        ->assertRedirect(route('home').'#xem-tuong')
        ->assertSessionHasErrors($field);

    expect(FaceAnalysis::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'terms not accepted' => [['agree_terms' => null], 'agree_terms'],
    'not an image' => [['portrait' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')], 'portrait'],
    'impossible date' => [['birth_day' => 31, 'birth_month' => 2], 'birth_date'],
    'unknown gender' => [['gender' => 'other'], 'gender'],
]);

test('result page shows the computed chart and locks paid sections', function () {
    $analysis = FaceAnalysis::factory()->create(['birth_date' => '1992-08-15', 'birth_hour' => 21, 'birth_minute' => 30]);

    $this->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSeeText('17 tháng 7 năm Nhâm Thân')
        ->assertSeeText('Kiếm Phong Kim')
        ->assertSeeText('Mậu Thân')
        ->assertSeeText('Thổ Ngũ Cục')
        ->assertSeeText('Phu Thê')
        ->assertSeeText('21:30 (giờ Hợi)')
        ->assertSeeText('Chưa mở khóa');
});

test('locked sections show every sub heading with blurred sample text', function () {
    $analysis = FaceAnalysis::factory()->create();

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('1. Về Thần (Tinh thần, Ý chí và Thần thái)')
        ->assertSeeText('2. Về Khí (Sinh khí, Năng lượng và Hơi thở sống)')
        ->assertSeeText('3. Về Sắc (Sắc diện, Màu sắc da và Vận hạn ngắn hạn)')
        ->assertSeeText('Tổng kết')
        ->assertSeeText('Tổng kết về Thượng đình')
        ->assertSeeText('3. Nhân trung và Vùng quanh miệng (Nối liền Trung và Hạ đình)')
        ->assertSeeText('Thái thính quan')
        ->assertSeeText('Phụ mẫu cung')
        ->assertSeeText('Hóa giải sát khí')
        ->assertSeeText('Cần thận trọng')
        ->assertSeeText('Mở khóa luận giải này chỉ')
        ->assertSee('tm-blurred', false)
        ->assertSee('aria-label="Khuôn mặt của', false);
});

test('sample text addresses the user by gender', function () {
    $this->get(route('analyses.show', FaceAnalysis::factory()->create(['gender' => 'female'])))
        ->assertSeeText('Thần thái của chị toát lên')
        ->assertDontSeeText('{anh}');

    $this->get(route('analyses.show', FaceAnalysis::factory()->create(['gender' => 'male'])))
        ->assertSeeText('Thần thái của anh toát lên');
});

test('real content of a locked section is never rendered', function () {
    $analysis = FaceAnalysis::factory()->create();
    FaceAnalysisSection::factory()->for($analysis)->type(AnalysisSection::ThanKhiSac)->create([
        'content' => ['tags' => ['Bí mật'], 'summary' => 'NỘI DUNG THẬT CHƯA TRẢ TIỀN', 'rating' => 5, 'blocks' => []],
    ]);

    $this->get(route('analyses.show', $analysis))
        ->assertDontSeeText('NỘI DUNG THẬT CHƯA TRẢ TIỀN')
        ->assertDontSeeText('Bí mật');
});

test('result page works without a birth time', function () {
    $analysis = FaceAnalysis::factory()->create(['birth_hour' => null, 'birth_minute' => null]);

    $this->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSeeText('Chưa rõ giờ sinh')
        ->assertSeeText('Cần giờ sinh');
});

test('unlocked sections are rendered', function () {
    $analysis = FaceAnalysis::factory()->create();
    $analysis->unlock(AnalysisSection::ThanKhiSac);
    $analysis->saveGeneratedContent(AnalysisSection::ThanKhiSac, [
        'tags' => [],
        'summary' => 'Thần thái điềm tĩnh.',
        'rating' => 4,
        'blocks' => [['title' => 'Tổng kết', 'items' => [['text' => 'Khí sắc ổn định.']]]],
    ], 'gemini-test');

    $this->get(route('analyses.show', $analysis))
        ->assertSeeText('Thần thái điềm tĩnh.')
        ->assertSeeText('Khí sắc ổn định.');
});

test('the chart reveal animation only plays on the first visit', function () {
    $analysis = FaceAnalysis::factory()->create();

    $this->get(route('analyses.show', $analysis))->assertSee('tm-lazy');
    $this->get(route('analyses.show', $analysis))->assertDontSee('tm-lazy');
    $this->get(route('analyses.show', FaceAnalysis::factory()->create()))->assertSee('tm-lazy');
});

test('unknown tokens return 404', function () {
    $this->get('/ket-qua/not-a-real-token')->assertNotFound();
});

test('the portrait is served only through the result token', function () {
    $analysis = FaceAnalysis::factory()->create(['portrait_path' => 'portraits/a.jpg']);
    Storage::disk('local')->put('portraits/a.jpg', 'image-bytes');

    $this->get(route('analyses.portrait', $analysis))->assertOk();
});

test('unlock buttons create pending orders with the configured price', function () {
    $analysis = FaceAnalysis::factory()->create();

    $this->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh'])
        ->assertRedirect(route('analyses.show', $analysis))
        ->assertSessionHas('payment_order');

    $this->post(route('analyses.unlock', $analysis))->assertRedirect(route('analyses.show', $analysis));

    expect($analysis->orders()->count())->toBe(2)
        ->and($analysis->orders()->first()->status)->toBe(AnalysisOrderStatus::Pending)
        ->and($analysis->orders()->first()->amount)->toBe(AnalysisSection::TamDinh->price());
});

test('unlocking from the page returns the payment popup without leaving the page', function () {
    $analysis = FaceAnalysis::factory()->create();

    $response = $this->postJson(route('analyses.unlock', $analysis), ['section' => 'tam_dinh'])->assertOk();

    expect($response->json('html'))->toContain('data-modal="payment"')
        ->toContain($analysis->orders()->sole()->code)
        ->toContain('Phần 3 · Tam đình');
});

test('free sections cannot be ordered', function () {
    $analysis = FaceAnalysis::factory()->create();

    $this->post(route('analyses.unlock', $analysis), ['section' => 'ngu_hanh'])->assertSessionHasErrors('section');
});
