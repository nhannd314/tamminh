<?php

namespace App\Http\Middleware;

use App\Support\AffiliateReferral;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ghi nhận link giới thiệu `?ref=` trên mọi trang GET.
 */
class CaptureAffiliateReferral
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->query->has('ref')) {
            AffiliateReferral::capture($request);
        }

        return $next($request);
    }
}
