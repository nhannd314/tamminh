<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cấu hình tài khoản nhận tiền (SePay). Khoá webhook là bí mật nên nằm ở .env, không ở đây.
     *
     * @var array<string, array{0: mixed, 1: string}>
     */
    private array $settings = [
        'payment.bank' => ['MBBank', 'Ngân hàng nhận tiền - mã ngân hàng theo SePay (VD: MBBank, Vietcombank, ACB, TPBank…)'],
        'payment.account_number' => ['', 'Số tài khoản nhận tiền (đã liên kết với SePay)'],
        'payment.account_name' => ['', 'Tên chủ tài khoản (in hoa, không dấu)'],
        'payment.transfer_content' => ['{code}', 'Nội dung chuyển khoản; {code} là mã đơn - phải khớp tiền tố mã thanh toán cấu hình trên SePay'],
        'payment.note' => ['Vui lòng giữ nguyên nội dung chuyển khoản. Hệ thống tự động mở khóa trong vòng 1-2 phút sau khi nhận được tiền.', 'Lưu ý hiển thị trong cửa sổ thanh toán'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->settings as $key => [$value, $description]) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => json_encode($value),
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys($this->settings))->delete();
    }
};
