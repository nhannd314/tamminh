<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use Illuminate\Contracts\View\View;

/**
 * Trang chi tiết chuyên gia. Chuyên gia đã ẩn trả 404.
 */
class ExpertController extends Controller
{
    public function show(Expert $expert): View
    {
        abort_unless($expert->is_active, 404);

        return view('pages.experts.show', ['expert' => $expert]);
    }
}
