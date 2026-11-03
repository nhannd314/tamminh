<?php

use App\AnalysisOrderStatus;
use App\AnalysisSection;
use App\Jobs\GenerateAnalysisSection;
use App\Models\AnalysisOrder;
use App\Models\FaceAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Việc sinh nội dung sau thanh toán được kiểm tra ở GeminiAnalysisTest.
    Queue::fake([GenerateAnalysisSection::class]);

    config([
        'services.sepay.webhook_key' => 'secret-key',
        'services.sepay.bank' => 'MBBank',
        'services.sepay.account_number' => '0123456789',
        'services.sepay.account_name' => 'Công ty Tâm Minh',
        'services.sepay.transfer_content' => '{code}',
    ]);
});

/**
 * @return array<string, mixed>
 */
function sepayPayload(AnalysisOrder $order, array $overrides = []): array
{
    return [
        'id' => 92704,
        'gateway' => 'MBBank',
        'transactionDate' => '2026-10-30 10:02:37',
        'accountNumber' => '0123456789',
        'code' => null,
        'content' => 'NGUYEN VAN A chuyen tien '.$order->code.' FT2603',
        'transferType' => 'in',
        'transferAmount' => $order->amount,
        'referenceCode' => 'FT26030123',
        ...$overrides,
    ];
}

function postSepay(array $payload, string $key = 'secret-key')
{
    return test()->postJson(route('webhooks.sepay'), $payload, ['Authorization' => 'Apikey '.$key]);
}

test('unlock opens the payment popup with bank details and a sepay qr', function () {
    $analysis = FaceAnalysis::factory()->create();

    $response = $this->followingRedirects()->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh']);

    $order = $analysis->orders()->sole();

    $response->assertOk()
        ->assertSee('data-modal-autoopen', false)
        ->assertSeeText('0123456789')
        ->assertSeeText('CONG TY TAM MINH')
        ->assertSeeText($order->code)
        ->assertSee('https://qr.sepay.vn/img?acc=0123456789&amp;bank=MBBank&amp;amount='.$order->amount.'&amp;des='.$order->code, false);
});

test('clicking unlock again reuses the pending order', function () {
    $analysis = FaceAnalysis::factory()->create();

    $this->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh'])->assertSessionHas('payment_order');
    $this->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh']);
    $this->post(route('analyses.unlock', $analysis));

    expect($analysis->orders()->count())->toBe(2);
});

test('the popup explains when the receiving account is not configured', function () {
    config(['services.sepay.account_number' => null]);
    $analysis = FaceAnalysis::factory()->create();

    $this->followingRedirects()->post(route('analyses.unlock', $analysis))
        ->assertSeeText('Tài khoản nhận tiền chưa được cấu hình');
});

test('sepay webhook requires the api key', function () {
    $order = AnalysisOrder::factory()->create();

    postSepay(sepayPayload($order), 'wrong-key')->assertUnauthorized();
    $this->postJson(route('webhooks.sepay'), sepayPayload($order))->assertUnauthorized();

    expect($order->fresh()->status)->toBe(AnalysisOrderStatus::Pending);
});

test('sepay webhook marks the matching order as paid and unlocks the section', function () {
    $analysis = FaceAnalysis::factory()->create();
    $order = $analysis->createOrder(AnalysisSection::TamDinh);

    postSepay(sepayPayload($order))->assertOk()->assertJson(['success' => true]);

    $order->refresh();

    expect($order->status)->toBe(AnalysisOrderStatus::Paid)
        ->and($order->payment_method)->toBe('sepay')
        ->and($order->transaction_id)->toBe('sepay:92704')
        ->and($analysis->refresh()->isUnlocked(AnalysisSection::TamDinh))->toBeTrue();

    $this->getJson(route('analyses.order-status', [$analysis, $order->code]))->assertJson(['paid' => true]);
});

test('waiting for the payment does not use up the rate limit of the content stream', function () {
    $analysis = FaceAnalysis::factory()->completed()->create();
    $order = $analysis->createOrder(AnalysisSection::TamDinh);

    foreach (range(1, 15) as $poll) {
        $this->getJson(route('analyses.order-status', [$analysis, $order->code]))->assertOk();
    }

    $this->get(route('analyses.stream', $analysis))->assertOk();
});

test('sepay webhook ignores outgoing, underpaid and unknown transfers', function (array $overrides) {
    $order = FaceAnalysis::factory()->create()->createOrder(AnalysisSection::TamDinh);

    postSepay(sepayPayload($order, $overrides))->assertOk()->assertJson(['success' => true]);

    expect($order->fresh()->status)->toBe(AnalysisOrderStatus::Pending);
})->with([
    'outgoing' => [['transferType' => 'out']],
    'underpaid' => [['transferAmount' => 1000]],
    'unknown code' => [['content' => 'chuyen tien TMZZZZZZZZ']],
]);

test('sepay webhook can be delivered twice safely', function () {
    $order = FaceAnalysis::factory()->create()->createOrder();

    postSepay(sepayPayload($order))->assertOk();
    postSepay(sepayPayload($order, ['id' => 99999]))->assertOk();

    expect($order->fresh()->transaction_id)->toBe('sepay:92704');
});

test('order codes are found in noisy bank transfer content', function () {
    expect(AnalysisOrder::findCodeIn('ib chuyen tien tmab12cd34 ft123'))->toBe('TMAB12CD34')
        ->and(AnalysisOrder::findCodeIn('khong co ma'))->toBeNull();
});

test('the payment popup tells customers how to reach the shop if the reading does not arrive', function () {
    config(['site.phone' => '0900-111-222', 'site.zalo' => '0987-654-321', 'site.email' => 'hotro@tamminh.test']);
    $analysis = FaceAnalysis::factory()->create();

    $response = $this->followingRedirects()->post(route('analyses.unlock', $analysis), ['section' => 'tam_dinh']);

    $response->assertSeeText('Đã thanh toán mà chưa nhận được luận giải?')
        ->assertSeeText($analysis->orders()->sole()->code)
        ->assertSee('https://zalo.me/84987654321', false)
        ->assertSee('tel:0900111222', false)
        ->assertSeeText('hotro@tamminh.test');
});
