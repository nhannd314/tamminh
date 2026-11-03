<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Product;
use App\Support\Cart;
use App\Support\ShopContact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Giỏ hàng trong session: xem, thêm, đổi số lượng và xoá dòng. Đặt hàng ở CheckoutController;
 * khách vẫn có thể chốt đơn qua Zalo bằng nội dung đơn hàng ở trang giỏ.
 */
class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function show(): View
    {
        $lines = $this->cart->lines();

        return view('pages.cart.show', [
            'lines' => $lines,
            'total' => $this->cart->total($lines),
            'zaloUrl' => ShopContact::zaloUrl(),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        if ($product->stock < 1) {
            $message = 'Vật phẩm này tạm hết hàng.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('toast', $message);
        }

        // Lựa chọn không hợp lệ (hoặc bỏ trống) thì dùng lựa chọn đầu tiên; khắc tên chỉ áp dụng khi vật phẩm cho phép.
        $sizes = collect($product->sizes ?? [])->pluck('name');
        $styles = collect($product->styles ?? []);
        $size = $sizes->contains($request->input('size')) ? $request->input('size') : $sizes->first();
        $style = $styles->contains($request->input('style')) ? $request->input('style') : $styles->first();
        $engraving = $product->allows_engraving ? trim((string) $request->input('engraving')) ?: null : null;
        $quantity = min($request->integer('quantity'), $product->stock);

        $this->cart->add($product, $quantity, $size, $style, $engraving);

        $message = "Đã thêm {$quantity} × {$product->name}".($size ? " ({$size})" : '').' vào giỏ hàng.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'count' => $this->cart->count()]);
        }

        return back()->with('toast', $message);
    }

    public function update(Request $request, string $line): RedirectResponse
    {
        $this->cart->setQuantity($line, $request->integer('quantity'));

        return to_route('cart.show');
    }

    public function destroy(string $line): RedirectResponse
    {
        $this->cart->remove($line);

        return to_route('cart.show')->with('toast', 'Đã xoá khỏi giỏ hàng.');
    }
}
