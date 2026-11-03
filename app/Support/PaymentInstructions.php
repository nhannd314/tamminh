<?php

namespace App\Support;

use App\Models\AnalysisOrder;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Thông tin chuyển khoản cho một đơn (mở khoá xem tướng hoặc vật phẩm): tài khoản nhận (.env, `services.sepay`)
 * + mã QR của SePay có sẵn số tiền và nội dung là mã đơn, để webhook SePay tự đối chiếu.
 */
final readonly class PaymentInstructions
{
    public function __construct(
        public AnalysisOrder|Order $order,
        public string $bank,
        public string $accountNumber,
        public string $accountName,
        public string $content,
        public string $note,
    ) {}

    public static function for(AnalysisOrder|Order $order): self
    {
        return new self(
            order: $order,
            bank: (string) config('services.sepay.bank'),
            accountNumber: (string) config('services.sepay.account_number'),
            accountName: Str::upper(Str::ascii((string) config('services.sepay.account_name'))),
            content: Str::replace('{code}', $order->code, (string) config('services.sepay.transfer_content')),
            note: (string) config('services.sepay.note'),
        );
    }

    public function isConfigured(): bool
    {
        return $this->bank !== '' && $this->accountNumber !== '';
    }

    public function qrUrl(): string
    {
        return config('services.sepay.qr_url').'?'.http_build_query([
            'acc' => $this->accountNumber,
            'bank' => $this->bank,
            'amount' => $this->order->amount,
            'des' => $this->content,
            'template' => 'compact',
        ]);
    }
}
