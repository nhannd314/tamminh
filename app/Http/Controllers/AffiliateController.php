<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAffiliateBankRequest;
use App\Models\Affiliate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Chương trình affiliate trong trang tài khoản: giới thiệu & tham gia, link giới thiệu,
 * thống kê hoa hồng và tài khoản ngân hàng nhận tiền.
 */
class AffiliateController extends Controller
{
    /**
     * Trang giới thiệu chương trình, xem được khi chưa đăng nhập. Khách đã đăng nhập thì nút chính dẫn vào trang affiliate của họ.
     */
    public function landing(Request $request): View
    {
        return view('pages.affiliate', [
            'customer' => $request->user('customer'),
            'isAffiliate' => (bool) $request->user('customer')?->affiliate()->exists(),
            'productPercent' => Affiliate::productCommissionPercent(),
            'analysisPercent' => Affiliate::analysisCommissionPercent(),
            'cookieDays' => Affiliate::cookieDays(),
        ]);
    }

    /**
     * Nút "Tạo tài khoản" ở trang giới thiệu: đăng ký (hoặc đăng nhập Google) xong thì vào thẳng trang affiliate.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->session()->put('url.intended', route('account.affiliate.show'));

        return to_route('register');
    }

    public function show(Request $request): View
    {
        $user = $request->user('customer');
        $affiliate = $user->affiliate()->first();

        return view('pages.account.affiliate', [
            'user' => $user,
            'affiliate' => $affiliate,
            'productPercent' => Affiliate::productCommissionPercent(),
            'analysisPercent' => Affiliate::analysisCommissionPercent(),
            'cookieDays' => Affiliate::cookieDays(),
            'totals' => $affiliate?->totalsByStatus(),
            'commissions' => $affiliate?->commissions()->with('commissionable')->latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->user('customer')->affiliate()->firstOrCreate();

        return to_route('account.affiliate.show')->with('toast', 'Bạn đã tham gia chương trình affiliate. Chia sẻ link để nhận hoa hồng!');
    }

    public function update(UpdateAffiliateBankRequest $request): RedirectResponse
    {
        $request->user('customer')->affiliate()->firstOrFail()->update($request->validated());

        return to_route('account.affiliate.show')->with('toast', 'Đã lưu tài khoản nhận hoa hồng.');
    }
}
