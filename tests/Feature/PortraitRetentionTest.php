<?php

use App\AnalysisSection;
use App\Jobs\GenerateAnalysisSection;
use App\Models\AnalysisOrder;
use App\Models\FaceAnalysis;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Queue::fake([GenerateAnalysisSection::class]);

    foreach (AnalysisSection::paid() as $section) {
        Setting::set("pricing.section.{$section->value}", 0);
    }

    Setting::set('pricing.section.tam_dinh', 20000);
    Setting::set('pricing.section.advice', 30000);
});

/**
 * Lượt xem có ảnh gốc và ảnh cắt trên disk, tạo cách đây `$days` ngày.
 */
function analysisWithPortrait(int $days): FaceAnalysis
{
    $analysis = FaceAnalysis::factory()->create([
        'portrait_path' => "portraits/{$days}.jpg",
        'face_crop_path' => "faces/{$days}.jpg",
        'created_at' => now()->subDays($days),
    ]);

    Storage::disk('local')->put($analysis->portrait_path, 'portrait');
    Storage::disk('local')->put($analysis->face_crop_path, 'face');

    return $analysis;
}

test('portraits are deleted once they are older than the retention period', function () {
    $expired = analysisWithPortrait(FaceAnalysis::PORTRAIT_RETENTION_DAYS);
    $recent = analysisWithPortrait(FaceAnalysis::PORTRAIT_RETENTION_DAYS - 1);

    $this->artisan('analyses:purge-portraits')->assertSuccessful();

    expect(Storage::disk('local')->allFiles())->toEqualCanonicalizing([$recent->portrait_path, $recent->face_crop_path])
        ->and($expired->refresh()->hasPortrait())->toBeFalse()
        ->and($expired->face_crop_path)->toBeNull()
        ->and($recent->refresh()->hasPortrait())->toBeTrue();

    $this->get(route('analyses.show', $expired))
        ->assertOk()
        ->assertSeeText('Ảnh chân dung đã được tự động xóa sau '.FaceAnalysis::PORTRAIT_RETENTION_DAYS.' ngày.');
    $this->get(route('analyses.portrait', $expired))->assertNotFound();
    $this->get(route('analyses.face', $expired))->assertNotFound();
});

test('portraits are kept while paid content is still being written', function () {
    $analysis = analysisWithPortrait(FaceAnalysis::PORTRAIT_RETENTION_DAYS + 5);
    $analysis->unlock(AnalysisSection::TamDinh);

    $this->artisan('analyses:purge-portraits')->assertSuccessful();

    expect($analysis->refresh()->hasPortrait())->toBeTrue();
    Storage::disk('local')->assertExists($analysis->portrait_path);
});

test('portraits are kept while a payment created in the last day may still arrive', function () {
    $analysis = analysisWithPortrait(FaceAnalysis::PORTRAIT_RETENTION_DAYS + 5);
    $analysis->createOrder(AnalysisSection::TamDinh);

    $this->artisan('analyses:purge-portraits')->assertSuccessful();

    expect($analysis->refresh()->hasPortrait())->toBeTrue();
});

test('old uploads that never became an analysis are deleted', function () {
    $disk = Storage::disk('local');
    $disk->put('portraits/abandoned.jpg', 'portrait');
    $disk->put('portraits/just-uploaded.jpg', 'portrait');
    touch($disk->path('portraits/abandoned.jpg'), now()->subDays(FaceAnalysis::PORTRAIT_RETENTION_DAYS + 1)->getTimestamp());

    $this->artisan('analyses:purge-portraits')->assertSuccessful();

    expect($disk->allFiles())->toBe(['portraits/just-uploaded.jpg']);
});

test('sections that need the portrait can no longer be bought once it is deleted', function () {
    $analysis = FaceAnalysis::factory()->withoutPortrait()->create();

    $this->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh'])->assertSessionHasErrors('section');

    expect($analysis->purchasableSections())->toBe([AnalysisSection::Advice])
        ->and($analysis->fullPackageQuote()['amount'])->toBe(AnalysisSection::Advice->bundledPrice());

    $this->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSeeText('không thể luận giải thêm phần này')
        ->assertSeeText('Đọc trọn 1 phần còn lại');
});

test('a full package paid after the portrait is deleted unlocks only the sections that can still be written', function () {
    $analysis = FaceAnalysis::factory()->withoutPortrait()->create();
    $order = AnalysisOrder::factory()->fullPackage()->for($analysis)->create();

    $order->markAsPaid();

    expect($analysis->refresh()->isUnlocked(AnalysisSection::Advice))->toBeTrue()
        ->and($analysis->isUnlocked(AnalysisSection::TamDinh))->toBeFalse();
    Queue::assertPushed(GenerateAnalysisSection::class, 1);
});
