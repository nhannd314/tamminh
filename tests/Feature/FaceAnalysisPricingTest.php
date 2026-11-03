<?php

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\Jobs\GenerateAnalysisSection;
use App\Models\AnalysisOrder;
use App\Models\FaceAnalysis;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Việc sinh nội dung sau thanh toán được kiểm tra ở GeminiAnalysisTest.
    Queue::fake([GenerateAnalysisSection::class]);

    foreach (AnalysisSection::cases() as $section) {
        if ($section !== AnalysisSection::Overview) {
            Setting::set("pricing.section.{$section->value}", 20000);
        }
    }

    Setting::set('pricing.section.ngu_hanh', 0);
    Setting::set('pricing.full_package_discount_percent', 20);
});

test('a face analysis gets an unguessable token used as its route key', function () {
    $analysis = FaceAnalysis::factory()->create();

    expect($analysis->token)->toHaveLength(48)
        ->and($analysis->getRouteKey())->toBe($analysis->token)
        ->and($analysis->toArray())->not->toHaveKey('portrait_path');
});

test('sections without a price are free and overview is always free', function () {
    $analysis = FaceAnalysis::factory()->create();

    expect($analysis->isUnlocked(AnalysisSection::Overview))->toBeTrue()
        ->and($analysis->isUnlocked(AnalysisSection::NguHanh))->toBeTrue()
        ->and($analysis->lockedSections())->toHaveCount(6);

    Setting::set('pricing.section.ngu_hanh', 20000);
    Setting::query()->firstWhere('key', 'pricing.section.advice')->delete();

    expect($analysis->isUnlocked(AnalysisSection::NguHanh))->toBeFalse()
        ->and($analysis->isUnlocked(AnalysisSection::Advice))->toBeTrue()
        ->and($analysis->lockedSections())->toHaveCount(6);
});

test('section prices are read from settings', function () {
    Setting::set('pricing.section.advice', 39000);

    $order = FaceAnalysis::factory()->create()->createOrder(AnalysisSection::Advice);

    expect($order->amount)->toBe(39000)
        ->and($order->discount)->toBe(0)
        ->and($order->status)->toBe(AnalysisOrderStatus::Pending)
        ->and($order->code)->toStartWith('TM');
});

test('paying for one section unlocks only that section before its content is generated', function () {
    $analysis = FaceAnalysis::factory()->create();

    $analysis->createOrder(AnalysisSection::TamDinh)->markAsPaid('txn-1', ['bank' => 'VCB']);

    $section = $analysis->refresh()->section(AnalysisSection::TamDinh);

    expect($analysis->isUnlocked(AnalysisSection::TamDinh))->toBeTrue()
        ->and($analysis->isUnlocked(AnalysisSection::NguQuan))->toBeFalse()
        ->and($section->isGenerated())->toBeFalse();
});

test('full package is the sum of paid section prices minus the package discount', function () {
    expect(FaceAnalysis::factory()->create()->fullPackageQuote())
        ->toBe(['subtotal' => 120000, 'discount' => 24000, 'amount' => 96000]);
});

test('each section bought separately is deducted at its discounted price', function () {
    $analysis = FaceAnalysis::factory()->create();
    $analysis->createOrder(AnalysisSection::TamDinh)->markAsPaid();

    expect($analysis->refresh()->fullPackageQuote())
        ->toBe(['subtotal' => 120000, 'discount' => 40000, 'amount' => 80000]);
});

test('pending orders do not count towards the upgrade deduction', function () {
    $analysis = FaceAnalysis::factory()->create();
    AnalysisOrder::factory()->for($analysis)->section(AnalysisSection::TamDinh)->create();

    expect($analysis->fullPackageQuote()['amount'])->toBe(96000);
});

test('paying for the full package unlocks every paid section', function () {
    $analysis = FaceAnalysis::factory()->create();

    $analysis->createOrder()->markAsPaid();

    expect($analysis->refresh()->isFullyUnlocked())->toBeTrue()
        ->and($analysis->fullPackageQuote()['amount'])->toBe(0);
});

test('marking an order as paid twice keeps the first payment', function () {
    $order = AnalysisOrder::factory()->create();

    $order->markAsPaid('txn-1');
    $order->markAsPaid('txn-2');

    expect($order->transaction_id)->toBe('txn-1');
});

test('birth time is formatted when the hour is known', function () {
    expect(FaceAnalysis::factory()->make(['birth_hour' => 7, 'birth_minute' => 5])->birth_time)->toBe('07:05')
        ->and(FaceAnalysis::factory()->make(['birth_hour' => null])->birth_time)->toBeNull();
});
