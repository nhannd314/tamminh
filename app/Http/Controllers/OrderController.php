<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\PaymentInstructions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Trang xác nhận đơn vật phẩm (truy cập bằng mã đơn ngẫu nhiên); đơn online chưa trả tiền thì hiện QR chuyển khoản.
 */
class OrderController extends Controller
{
    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('pages.orders.show', [
            'order' => $order,
            'payment' => $order->awaitsOnlinePayment() ? PaymentInstructions::for($order) : null,
        ]);
    }

    /**
     * Trang đơn hỏi định kỳ để tự tải lại khi SePay báo đã nhận tiền.
     */
    public function paymentStatus(Order $order): JsonResponse
    {
        return response()->json(['paid' => $order->isPaid()]);
    }
}
