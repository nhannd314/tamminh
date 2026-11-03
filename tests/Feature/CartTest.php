<?php

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sizedProduct(array $overrides = []): Product
{
    return Product::factory()->create([
        'name' => 'Tỳ hưu bạc',
        'price' => 1290000,
        'stock' => 5,
        'sizes' => [
            ['name' => 'Nhỏ · 3cm', 'price' => 1290000, 'compare_price' => 1590000],
            ['name' => 'Vừa · 5cm', 'price' => 1890000, 'compare_price' => null],
        ],
        'styles' => ['Mặt dây chuyền', 'Tượng để bàn'],
        'allows_engraving' => true,
        ...$overrides,
    ]);
}

test('an empty cart shows a prompt to browse the shop', function () {
    $this->get(route('cart.show'))
        ->assertOk()
        ->assertSeeText('Giỏ hàng đang trống')
        ->assertSee(route('products.index'), false);
});

test('adding a product puts it in the cart with the chosen options and server side price', function () {
    $product = sizedProduct();

    $this->post(route('cart.store'), [
        'product_id' => $product->getKey(),
        'quantity' => 2,
        'size' => 'Vừa · 5cm',
        'style' => 'Tượng để bàn',
        'engraving' => 'Minh',
    ])->assertRedirect()->assertSessionHas('toast');

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertSeeText('Tỳ hưu bạc')
        ->assertSeeText('Vừa · 5cm · Tượng để bàn · Khắc: Minh')
        ->assertSeeText('3.780.000đ');
});

test('invalid options fall back to the first option and quantity is capped by stock', function () {
    $product = sizedProduct(['stock' => 3]);

    $this->post(route('cart.store'), [
        'product_id' => $product->getKey(),
        'quantity' => 50,
        'size' => 'Không tồn tại',
        'style' => '',
    ]);

    $line = app(Cart::class)->lines()->sole();

    expect($line->size)->toBe('Nhỏ · 3cm')
        ->and($line->style)->toBe('Mặt dây chuyền')
        ->and($line->quantity)->toBe(3)
        ->and($line->unitPrice)->toBe(1290000);
});

test('adding the same product with the same options adds up the quantity', function () {
    $product = sizedProduct();
    $payload = ['product_id' => $product->getKey(), 'quantity' => 1, 'size' => 'Nhỏ · 3cm', 'style' => 'Mặt dây chuyền'];

    $this->post(route('cart.store'), $payload);
    $this->post(route('cart.store'), $payload);

    expect(app(Cart::class)->lines()->sole()->quantity)->toBe(2);
});

test('quantity can be changed and a line removed', function () {
    $product = sizedProduct();
    $this->post(route('cart.store'), ['product_id' => $product->getKey(), 'quantity' => 1]);
    $key = app(Cart::class)->lines()->sole()->key;

    $this->patch(route('cart.update', $key), ['quantity' => 4])->assertRedirect(route('cart.show'));
    expect(app(Cart::class)->lines()->sole()->quantity)->toBe(4);

    $this->delete(route('cart.destroy', $key))->assertRedirect(route('cart.show'));
    expect(app(Cart::class)->lines())->toBeEmpty();
});

test('inactive or sold out products cannot be added', function () {
    $inactive = Product::factory()->inactive()->create();
    $soldOut = Product::factory()->create(['stock' => 0]);

    $this->post(route('cart.store'), ['product_id' => $inactive->getKey(), 'quantity' => 1])->assertSessionHasErrors('product_id');

    $this->post(route('cart.store'), ['product_id' => $soldOut->getKey(), 'quantity' => 1])->assertSessionHas('toast');
    expect(app(Cart::class)->lines())->toBeEmpty();
});

test('the header cart button shows how many items are in the cart', function () {
    $product = sizedProduct();
    $this->post(route('cart.store'), ['product_id' => $product->getKey(), 'quantity' => 3]);

    $this->get(route('home'))->assertSee('Giỏ hàng (3 sản phẩm)', false);
});
