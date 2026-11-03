<?php

use App\Events\ActivityOccurred;
use App\Models\Product;
use App\Support\ActivityFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('fake command broadcasts one activity and then waits a random few minutes', function () {
    Event::fake([ActivityOccurred::class]);
    config(['activity.fake_enabled' => true, 'activity.fake_interval_minutes' => ['min' => 2, 'max' => 6]]);
    Cache::forget('activity.fake.next_at');

    $this->artisan('activity:fake')->assertSuccessful();
    $this->artisan('activity:fake')->assertSuccessful();

    Event::assertDispatchedTimes(ActivityOccurred::class, 1);

    $waitSeconds = Cache::get('activity.fake.next_at') - now()->timestamp;
    expect($waitSeconds)->toBeGreaterThanOrEqual(115)->toBeLessThanOrEqual(360);
});

test('fake command does nothing when disabled', function () {
    Event::fake([ActivityOccurred::class]);
    config(['activity.fake_enabled' => false]);

    $this->artisan('activity:fake --force')->assertSuccessful();

    Event::assertNotDispatched(ActivityOccurred::class);
});

test('fake activities come from the data file with every placeholder filled', function () {
    foreach (range(1, 50) as $_) {
        $activity = ActivityFeed::fake();

        expect($activity['kind'])->toBeIn(['analysis', 'unlock', 'palm', 'cart', 'order', 'paid'])
            ->and($activity['message'])->not->toContain('{');
    }
});

test('display name only exposes the given name', function () {
    expect(ActivityFeed::displayName('Nguyễn Văn An'))->toBe('An')
        ->and(ActivityFeed::displayName(null))->toBe('Một khách hàng');
});

test('adding a product to the cart announces it without leaking personal details', function () {
    Event::fake([ActivityOccurred::class]);
    $product = Product::factory()->create(['stock' => 5, 'name' => 'Vòng tay thạch anh']);

    $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1]);

    Event::assertDispatched(ActivityOccurred::class, fn (ActivityOccurred $event): bool => $event->kind === 'cart'
        && str_contains($event->message, 'Vòng tay thạch anh'));
});

test('a broadcast failure never breaks the visitor action', function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.options.host' => '127.0.0.1',
        'broadcasting.connections.reverb.options.port' => 1,
        'broadcasting.connections.reverb.options.scheme' => 'http',
        'broadcasting.connections.reverb.options.useTLS' => false,
    ]);
    $product = Product::factory()->create(['stock' => 5]);

    $this->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 1])->assertRedirect();
});
