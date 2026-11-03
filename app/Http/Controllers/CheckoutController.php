<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\OrderPaymentMethod;
use App\Support\ActivityFeed;
use App\Support\AffiliateReferral;
use App\Support\Cart;
use App\Support\CartLine;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Trang thanh toán: khách nhập địa chỉ, chọn COD (tính phí giao hàng) hoặc chuyển khoản online (miễn phí giao hàng).
 * Đặt hàng chốt giá, trừ tồn kho và báo admin qua email.
 */
class CheckoutController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function show(): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return to_route('cart.show');
        }

        return view('pages.checkout.show', [
            'lines' => $lines,
            'subtotal' => $this->cart->total($lines),
            'shippingFees' => collect(OrderPaymentMethod::cases())
                ->mapWithKeys(fn (OrderPaymentMethod $method): array => [$method->value => $method->shippingFee()])
                ->all(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return to_route('cart.show')->with('toast', 'Giỏ hàng đang trống.');
        }

        $paymentMethod = $request->enum('payment_method', OrderPaymentMethod::class);

        $order = DB::transaction(function () use ($request, $lines, $paymentMethod): ?Order {
            $products = Product::query()->whereKey($lines->pluck('product.id'))->lockForUpdate()->get()->keyBy('id');

            // Tồn kho có thể đã giảm từ lúc khách mở giỏ: không đủ thì quay lại giỏ để xem số lượng mới.
            $quantities = $lines->groupBy(fn (CartLine $line): int => $line->product->id)->map->sum('quantity');

            foreach ($quantities as $productId => $quantity) {
                if (($products->get($productId)?->stock ?? 0) < $quantity) {
                    return null;
                }
            }

            $subtotal = $this->cart->total($lines);
            $shippingFee = $paymentMethod->shippingFee();

            $order = Order::create([
                'user_id' => $request->user('customer')?->id,
                'affiliate_id' => AffiliateReferral::resolve($request)?->id,
                'customer_name' => $request->string('customer_name')->trim()->toString(),
                'phone' => $request->string('phone')->toString(),
                'address' => $request->string('address')->trim()->toString(),
                'note' => $request->string('note')->trim()->toString() ?: null,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
            ]);

            $order->items()->createMany($lines->map(fn (CartLine $line): array => [
                'product_id' => $line->product->id,
                'product_name' => $line->product->name,
                'size' => $line->size,
                'style' => $line->style,
                'engraving' => $line->engraving,
                'unit_price' => $line->unitPrice,
                'quantity' => $line->quantity,
                'subtotal' => $line->subtotal(),
            ])->all());

            foreach ($quantities as $productId => $quantity) {
                $products->get($productId)->decrement('stock', $quantity);
            }

            return $order;
        });

        if ($order === null) {
            return to_route('cart.show')->with('toast', 'Một số vật phẩm vừa hết hàng, vui lòng kiểm tra lại giỏ.');
        }

        $this->cart->clear();

        ActivityFeed::announce('order', ActivityFeed::displayName($order->customer_name)." vừa đặt hàng ({$lines->sum('quantity')} vật phẩm)");

        $adminEmails = User::query()->where('role', UserRole::Admin)->pluck('email');

        if ($adminEmails->isNotEmpty()) {
            Mail::to($adminEmails)->queue(new OrderPlaced($order));
        }

        return to_route('orders.show', $order);
    }
}
