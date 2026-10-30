<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook SePay báo có giao dịch vào tài khoản. Tìm mã đơn trong nội dung chuyển khoản,
 * đối chiếu số tiền rồi đánh dấu đã thanh toán (mở khoá nội dung).
 *
 * Luôn trả {"success": true} cho các giao dịch hợp lệ về mặt chứng thực - kể cả khi không khớp đơn -
 * để SePay không gửi lại; trường hợp không khớp được ghi log để đối soát thủ công.
 *
 * @see https://docs.sepay.vn/tich-hop-webhooks.html
 */
class SepayWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $key = config('services.sepay.webhook_key');

        abort_if(blank($key) || ! hash_equals('Apikey '.$key, (string) $request->header('Authorization')), 401);

        if ($request->input('transferType') !== 'in') {
            return $this->ok();
        }

        $code = Order::findCodeIn($request->input('code').' '.$request->input('content'));
        $order = $code ? Order::query()->firstWhere('code', $code) : null;

        if ($order === null) {
            Log::info('SePay: giao dịch không khớp đơn nào', $request->only('id', 'content', 'transferAmount'));

            return $this->ok();
        }

        if ((int) $request->input('transferAmount') < $order->amount) {
            Log::warning("SePay: đơn {$order->code} chuyển thiếu tiền", $request->only('id', 'content', 'transferAmount'));

            return $this->ok();
        }

        $order->markAsPaid('sepay:'.$request->input('id'), $request->all(), 'sepay');

        return $this->ok();
    }

    private function ok(): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
