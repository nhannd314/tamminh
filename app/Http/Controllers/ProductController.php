<?php

namespace App\Http\Controllers;

use App\FiveElement;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\ShopContact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Cửa hàng vật phẩm phong thủy: danh sách lọc theo bản mệnh, nhóm hàng, sắp xếp và trang chi tiết.
 * Vật phẩm đã ẩn trả 404.
 */
class ProductController extends Controller
{
    private const PER_PAGE = 24;

    private const RELATED_LIMIT = 4;

    public function index(Request $request): View
    {
        $element = FiveElement::tryFrom((string) $request->query('menh'));
        $category = ProductCategory::query()->where('slug', (string) $request->query('nhom'))->first();
        $sort = in_array($request->query('sap-xep'), ['gia-tang', 'gia-giam'], true) ? $request->query('sap-xep') : 'pho-bien';

        $products = Product::query()
            ->active()
            ->with('category')
            ->when($element, fn ($query) => $query->forElement($element))
            ->when($category, fn ($query) => $query->whereBelongsTo($category, 'category'))
            ->when($sort === 'gia-tang', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'gia-giam', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'pho-bien', fn ($query) => $query->orderByDesc('sold_count')->orderBy('sort'))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('pages.products.index', [
            'products' => $products,
            'element' => $element,
            'category' => $category,
            'sort' => $sort,
            'categories' => ProductCategory::query()->whereHas('products', fn ($query) => $query->active())->orderBy('sort')->orderBy('name')->get(),
            'zaloUrl' => ShopContact::zaloUrl(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        $related = Product::query()
            ->active()
            ->with('category')
            ->whereKeyNot($product->getKey())
            ->orderByRaw('product_category_id = ? desc', [$product->product_category_id])
            ->orderByDesc('sold_count')
            ->limit(self::RELATED_LIMIT)
            ->get();

        return view('pages.products.show', [
            'product' => $product,
            'related' => $related,
            'zaloUrl' => ShopContact::zaloUrl(),
        ]);
    }
}
