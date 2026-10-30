<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Thông tin chuyển khoản cho một đơn: tài khoản nhận (bảng settings) + mã QR của SePay
 * có sẵn số tiền và nội dung là mã đơn, để webhook SePay tự đối chiếu và mở khoá.
 */
final readonly class PaymentInstructions
{
    public function __construct(
        public Order $order,
        public string $bank,
        public string $accountNumber,
        public string $accountName,
        public string $content,
        public string $note,
    ) {}

    public static function for(Order $order): self
    {
        return new self(
            order: $order,
            bank: (string) Setting::get('payment.bank', ''),
            accountNumber: (string) Setting::get('payment.account_number', ''),
            accountName: (string) Setting::get('payment.account_name', ''),
            content: Str::replace('{code}', $order->code, (string) Setting::get('payment.transfer_content', '{code}')),
            note: (string) Setting::get('payment.note', ''),
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
