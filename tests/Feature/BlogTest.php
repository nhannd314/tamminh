<?php

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the blog lists only published posts, newest first', function () {
    Post::factory()->create(['title' => 'Bài cũ hơn', 'published_at' => now()->subDays(5)]);
    Post::factory()->create(['title' => 'Bài mới nhất', 'published_at' => now()->subDay()]);
    Post::factory()->draft()->create(['title' => 'Bản nháp chưa đăng']);
    Post::factory()->scheduled()->create(['title' => 'Bài hẹn giờ ngày mai']);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSeeTextInOrder(['Bài mới nhất', 'Bài cũ hơn'])
        ->assertDontSeeText('Bản nháp chưa đăng')
        ->assertDontSeeText('Bài hẹn giờ ngày mai');
});

test('a category page lists only the posts of that category', function () {
    $category = PostCategory::factory()->create(['name' => 'Tử vi', 'slug' => 'tu-vi']);
    Post::factory()->for($category, 'category')->create(['title' => 'Vận năm Bính Ngọ']);
    Post::factory()->create(['title' => 'Bài chuyên mục khác']);

    $this->get(route('posts.category', $category))
        ->assertOk()
        ->assertSeeText('Vận năm Bính Ngọ')
        ->assertDontSeeText('Bài chuyên mục khác');
});

test('the blog is paginated', function () {
    Post::factory()->count(10)->sequence(fn ($sequence) => ['published_at' => now()->subDays($sequence->index + 1)])->create();
    $oldest = Post::query()->oldest('published_at')->first();

    $this->get(route('posts.index'))->assertDontSeeText($oldest->title);
    $this->get(route('posts.index', ['page' => 2]))
        ->assertSeeText($oldest->title)
        ->assertSee('noindex, follow', false);
});

test('a published post shows its content and related posts of the same category', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->for($category, 'category')->create([
        'content' => '<h2>Thần: ánh mắt</h2><p>Mắt có thần là mắt sáng.</p>',
        'author_name' => 'Thầy Minh Quang',
    ]);
    $related = Post::factory()->for($category, 'category')->create();
    $unrelated = Post::factory()->create();

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('<h2>Thần: ánh mắt</h2>', false)
        ->assertSeeText('Thầy Minh Quang')
        ->assertSeeText('Bài viết cùng chuyên mục')
        ->assertSeeText($related->title)
        ->assertDontSeeText($unrelated->title);
});

test('drafts and scheduled posts cannot be opened', function (Post $post) {
    $this->get(route('posts.show', $post))->assertNotFound();
})->with([
    'draft' => fn () => Post::factory()->draft()->create(),
    'scheduled' => fn () => Post::factory()->scheduled()->create(),
]);

test('reading time is estimated from the length of the content', function () {
    $post = Post::factory()->make(['content' => '<p>'.str_repeat('chữ ', 660).'</p>']);

    expect($post->readingMinutes())->toBe(3)
        ->and(Post::factory()->make(['content' => '<p>Ngắn.</p>'])->readingMinutes())->toBe(1);
});

test('the home page shows the three latest posts and hides the section when there are none', function () {
    $this->get(route('home'))->assertDontSeeText('Bài viết & tư liệu');

    $posts = Post::factory()->count(4)->sequence(fn ($sequence) => ['published_at' => now()->subDays($sequence->index + 1)])->create();

    $this->get(route('home'))
        ->assertSeeText('Bài viết & tư liệu')
        ->assertSee(route('posts.show', $posts[0]), false)
        ->assertSee(route('posts.show', $posts[2]), false)
        ->assertDontSee(route('posts.show', $posts[3]), false);
});
