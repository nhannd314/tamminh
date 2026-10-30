<?php

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Models\Order;
use App\Models\Setting;
use App\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (AnalysisSection::cases() as $section) {
        if ($section !== AnalysisSection::Overview) {
            Setting::set("pricing.section.{$section->value}", 50000);
        }
    }

    Setting::set('pricing.full_package', 200000);
    Setting::set('pricing.upgrade_credit_percent', 50);
    Setting::set('analysis.free_sections', ['ngu_hanh']);
});

test('a face analysis gets an unguessable token used as its route key', function () {
    $analysis = FaceAnalysis::factory()->create();

    expect($analysis->token)->toHaveLength(48)
        ->and($analysis->getRouteKey())->toBe($analysis->token)
        ->and($analysis->toArray())->not->toHaveKey('portrait_path');
});

test('free sections come from settings and overview is always free', function () {
    $analysis = FaceAnalysis::factory()->create();

    expect($analysis->isUnlocked(AnalysisSection::Overview))->toBeTrue()
        ->and($analysis->isUnlocked(AnalysisSection::NguHanh))->toBeTrue()
        ->and($analysis->lockedSections())->toHaveCount(6);

    Setting::set('analysis.free_sections', []);

    expect($analysis->isUnlocked(AnalysisSection::NguHanh))->toBeFalse()
        ->and($analysis->lockedSections())->toHaveCount(7);
});

test('section prices are read from settings', function () {
    Setting::set('pricing.section.advice', 39000);

    $order = FaceAnalysis::factory()->create()->createOrder(AnalysisSection::Advice);

    expect($order->amount)->toBe(39000)
        ->and($order->discount)->toBe(0)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->code)->toStartWith('TM');
});

test('paying for one section unlocks only that section without generating content', function () {
    $analysis = FaceAnalysis::factory()->create();

    $analysis->createOrder(AnalysisSection::TamDinh)->markAsPaid('txn-1', ['bank' => 'VCB']);

    $section = $analysis->refresh()->section(AnalysisSection::TamDinh);

    expect($analysis->isUnlocked(AnalysisSection::TamDinh))->toBeTrue()
        ->and($analysis->isUnlocked(AnalysisSection::NguQuan))->toBeFalse()
        ->and($section->isGenerated())->toBeFalse();
});

test('full package costs the configured price when nothing was bought', function () {
    expect(FaceAnalysis::factory()->create()->fullPackageQuote())
        ->toBe(['subtotal' => 200000, 'discount' => 0, 'amount' => 200000]);
});

test('full package is discounted by a percentage of what was spent on single sections', function () {
    $analysis = FaceAnalysis::factory()->create();
    $analysis->createOrder(AnalysisSection::TamDinh)->markAsPaid();
    $analysis->createOrder(AnalysisSection::NguQuan)->markAsPaid();

    expect($analysis->refresh()->fullPackageQuote())
        ->toBe(['subtotal' => 200000, 'discount' => 50000, 'amount' => 150000]);
});

test('full package never costs more than the remaining single sections', function () {
    $analysis = FaceAnalysis::factory()->create();

    foreach ([AnalysisSection::ThanKhiSac, AnalysisSection::TamDinh, AnalysisSection::NguQuan, AnalysisSection::ThapNhiCung, AnalysisSection::PhongThuy] as $section) {
        $analysis->createOrder($section)->markAsPaid();
    }

    expect($analysis->refresh()->fullPackageQuote()['amount'])->toBe(50000);
});

test('pending orders do not count towards the upgrade discount', function () {
    $analysis = FaceAnalysis::factory()->create();
    Order::factory()->for($analysis)->section(AnalysisSection::TamDinh)->create();

    expect($analysis->fullPackageQuote()['discount'])->toBe(0);
});

test('paying for the full package unlocks every paid section', function () {
    $analysis = FaceAnalysis::factory()->create();

    $analysis->createOrder()->markAsPaid();

    expect($analysis->refresh()->isFullyUnlocked())->toBeTrue()
        ->and($analysis->fullPackageQuote()['amount'])->toBe(0);
});

test('marking an order as paid twice keeps the first payment', function () {
    $order = Order::factory()->create();

    $order->markAsPaid('txn-1');
    $order->markAsPaid('txn-2');

    expect($order->transaction_id)->toBe('txn-1');
});

test('birth time is formatted when the hour is known', function () {
    expect(FaceAnalysis::factory()->make(['birth_hour' => 7, 'birth_minute' => 5])->birth_time)->toBe('07:05')
        ->and(FaceAnalysis::factory()->make(['birth_hour' => null])->birth_time)->toBeNull();
});
