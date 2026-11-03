<?php

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Products\ProductResource;
use App\FiveElement;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('shop lists active products and hides inactive ones', function () {
    Product::factory()->create(['name' => 'Tỳ hưu bạc']);
    Product::factory()->inactive()->create(['name' => 'Vật phẩm đã ẩn']);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSeeText('Tỳ hưu bạc')
        ->assertDontSeeText('Vật phẩm đã ẩn');
});

test('shop filters by element and category', function () {
    $stones = ProductCategory::factory()->create(['slug' => 'da']);
    Product::factory()->for($stones, 'category')->create(['name' => 'Đá hợp Kim', 'elements' => ['kim']]);
    Product::factory()->for($stones, 'category')->create(['name' => 'Đá hợp Hỏa', 'elements' => ['hoa']]);
    Product::factory()->create(['name' => 'Cây hợp Kim', 'elements' => ['kim']]);

    $this->get(route('products.index', ['menh' => 'kim', 'nhom' => 'da']))
        ->assertOk()
        ->assertSeeText('Đá hợp Kim')
        ->assertDontSeeText('Đá hợp Hỏa')
        ->assertDontSeeText('Cây hợp Kim');
});

test('shop sorts by price', function () {
    Product::factory()->create(['name' => 'Món đắt', 'price' => 900000]);
    Product::factory()->create(['name' => 'Món rẻ', 'price' => 100000]);

    $this->get(route('products.index', ['sap-xep' => 'gia-tang']))->assertSeeTextInOrder(['Món rẻ', 'Món đắt']);
    $this->get(route('products.index', ['sap-xep' => 'gia-giam']))->assertSeeTextInOrder(['Món đắt', 'Món rẻ']);
});

test('shop shows an empty state when nothing matches', function () {
    $this->get(route('products.index', ['menh' => 'thuy']))
        ->assertOk()
        ->assertSeeText('Chưa có vật phẩm phù hợp');
});

test('product page shows sizes, content tabs, reviews and zalo advice', function () {
    $product = Product::factory()->create([
        'name' => 'Tỳ hưu bạc',
        'sku' => 'TH-B925',
        'elements' => ['kim', 'thuy'],
        'price' => 1290000,
        'sizes' => [
            ['name' => 'Nhỏ · 3cm', 'price' => 1290000, 'compare_price' => 1590000],
            ['name' => 'Vừa · 5cm', 'price' => 1890000, 'compare_price' => null],
        ],
        'styles' => ['Mặt dây chuyền'],
        'allows_engraving' => true,
        'meaning' => 'Biểu tượng chiêu tài.',
        'usage' => ['Đeo hướng ra ngoài.'],
        'specs' => [['label' => 'Chất liệu', 'value' => 'Bạc S925']],
        'reviews' => [['rating' => 5, 'name' => 'Anh Hoàng Long', 'note' => 'Mệnh Kim', 'quote' => 'Rất tốt.']],
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSeeText('Hợp mệnh Kim')
        ->assertSeeText('Hợp mệnh Thủy')
        ->assertSeeText('TH-B925')
        ->assertSeeText('1.290.000đ')
        ->assertSeeText('1.590.000đ')
        ->assertSeeText('Vừa · 5cm')
        ->assertSeeText('Mặt dây chuyền')
        ->assertSeeText('Khắc tên')
        ->assertSeeText('Biểu tượng chiêu tài.')
        ->assertSeeText('Đeo hướng ra ngoài.')
        ->assertSeeText('Bạc S925')
        ->assertSeeText('Anh Hoàng Long')
        ->assertSee('https://zalo.me/', false);
});

test('product page without optional content still renders', function () {
    $product = Product::factory()->create(['sizes' => null, 'styles' => null, 'meaning' => null, 'usage' => null, 'specs' => null, 'reviews' => null]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertDontSeeText('Kích thước')
        ->assertDontSeeText('Đánh giá từ khách hàng');
});

test('out of stock product hides the buy buttons', function () {
    $product = Product::factory()->create(['stock' => 0]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSeeText('tạm hết hàng')
        ->assertDontSeeText('Thêm vào giỏ');
});

test('inactive products return 404', function () {
    $product = Product::factory()->inactive()->create();

    $this->get(route('products.show', $product))->assertNotFound();
});

test('product elements are stored as five element enums', function () {
    $product = Product::factory()->create(['elements' => ['kim', 'hoa']]);

    expect($product->fresh()->elements->all())->toBe([FiveElement::Kim, FiveElement::Hoa])
        ->and($product->elementLabels())->toBe('Kim · Hỏa');
});

test('admins can open the shop screens and create a product', function () {
    Filament::setCurrentPanel('admin');
    $this->actingAs(User::factory()->admin()->create());
    $category = ProductCategory::factory()->create();

    $this->get(ProductResource::getUrl('index'))->assertOk();
    $this->get(ProductCategoryResource::getUrl('index'))->assertOk();

    Livewire::test(ManageProducts::class)
        ->callAction('create', [
            'name' => 'Vòng thạch anh',
            'slug' => 'vong-thach-anh',
            'product_category_id' => $category->getKey(),
            'summary' => 'Thanh lọc khí',
            'elements' => ['kim', 'thuy'],
            'glyph' => '晶',
            'price' => 690000,
            'stock' => 5,
            'sold_count' => 0,
            'reviews_count' => 0,
            'is_active' => true,
        ])
        ->assertHasNoFormErrors();

    expect(Product::query()->where('slug', 'vong-thach-anh')->first()->elements->all())->toBe([FiveElement::Kim, FiveElement::Thuy]);
});
