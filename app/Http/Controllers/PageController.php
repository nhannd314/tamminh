<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

/**
 * Trang nội dung tĩnh tại `/{slug}`; trang đang ẩn trả 404.
 */
class PageController extends Controller
{
    public function show(Page $page): View
    {
        abort_unless($page->is_published, 404);

        return view('pages.page', [
            'page' => $page,
            'isContact' => $page->slug === 'lien-he',
        ]);
    }
}
