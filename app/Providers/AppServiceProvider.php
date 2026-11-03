<?php

namespace App\Providers;

use App\Models\AnalysisOrder;
use App\Models\Order;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tên ngắn, ổn định cho cột `commissionable_type` của hoa hồng affiliate (không phụ thuộc tên class).
        Relation::morphMap([
            'order' => Order::class,
            'analysis_order' => AnalysisOrder::class,
        ]);

        ResetPassword::toMailUsing(fn (object $notifiable, string $token): MailMessage => (new MailMessage)
            ->subject('Đặt lại mật khẩu tài khoản Tâm Minh')
            ->greeting('Xin chào '.$notifiable->name.',')
            ->line('Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản Tâm Minh của bạn.')
            ->action('Đặt mật khẩu mới', route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('Liên kết có hiệu lực trong '.config('auth.passwords.users.expire').' phút.')
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này - mật khẩu hiện tại vẫn giữ nguyên.')
            ->salutation('Tâm Minh'));
    }
}
