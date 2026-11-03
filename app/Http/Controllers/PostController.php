<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Contracts\View\View;

/**
 * Trang kiến thức: danh sách bài viết (tất cả hoặc theo chuyên mục) và trang chi tiết.
 * Bản nháp và bài hẹn giờ chưa tới ngày đăng đều trả 404.
 */
class PostController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        return $this->listing();
    }

    public function category(PostCategory $category): View
    {
        return $this->listing($category);
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->load('category');

        $related = Post::query()
            ->published()
            ->with('category')
            ->whereBelongsTo($post->category, 'category')
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.posts.show', ['post' => $post, 'related' => $related]);
    }

    private function listing(?PostCategory $category = null): View
    {
        $posts = Post::query()
            ->published()
            ->with('category')
            ->when($category, fn ($query) => $query->whereBelongsTo($category, 'category'))
            ->latest('published_at')
            ->paginate(self::PER_PAGE);

        return view('pages.posts.index', [
            'posts' => $posts,
            'category' => $category,
            'categories' => PostCategory::query()->whereHas('posts', fn ($query) => $query->published())->orderBy('sort')->orderBy('name')->get(),
        ]);
    }
}
