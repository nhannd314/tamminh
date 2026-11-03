<?php

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\PostCategories\PostCategoryResource;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\PostTone;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('admin');
});

test('admins can open the content screens', function (string $url) {
    Post::factory()->create(['title' => 'Bài trong danh sách']);

    $this->actingAs(User::factory()->admin()->create())
        ->get($url)
        ->assertOk();
})->with([
    'posts' => fn () => PostResource::getUrl('index'),
    'new post' => fn () => PostResource::getUrl('create'),
    'categories' => fn () => PostCategoryResource::getUrl('index'),
    'pages' => fn () => PageResource::getUrl('index'),
]);

test('regular users cannot open the content screens', function () {
    $this->actingAs(User::factory()->create())
        ->get(PostResource::getUrl('index'))
        ->assertForbidden();
});

test('an admin can write a post that is published right away', function () {
    $this->actingAs(User::factory()->admin()->create());
    $category = PostCategory::factory()->create();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Ý nghĩa ấn đường',
            'slug' => 'y-nghia-an-duong',
            'excerpt' => 'Ấn đường là vùng giữa hai lông mày.',
            'content' => '<p>Ấn đường rộng, sáng.</p>',
            'post_category_id' => $category->getKey(),
            'author_name' => 'Thầy Minh Quang',
            'glyph' => '印',
            'tone' => PostTone::Navy,
            'published_at' => now()->subMinute(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->firstWhere('slug', 'y-nghia-an-duong');

    expect($post->isPublished())->toBeTrue()
        ->and($post->category->is($category))->toBeTrue();

    $this->get(route('posts.show', $post))->assertOk()->assertSeeText('Ấn đường rộng, sáng.');
});

test('post slugs must be unique and url safe', function (string $slug) {
    $this->actingAs(User::factory()->admin()->create());
    $existing = Post::factory()->create(['slug' => 'da-co']);

    Livewire::test(EditPost::class, ['record' => Post::factory()->create()->getRouteKey()])
        ->fillForm(['slug' => $slug])
        ->call('save')
        ->assertHasFormErrors(['slug']);

    expect($existing->refresh()->slug)->toBe('da-co');
})->with([
    'taken' => 'da-co',
    'with spaces' => 'co dau cach',
]);

test('page slugs cannot take over the paths used by the site', function (string $slug) {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(CreatePage::class)
        ->fillForm(['title' => 'Thử', 'slug' => $slug, 'content' => '<p>Nội dung</p>'])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(Page::query()->count())->toBe(0);
})->with([
    'admin panel' => 'admin',
    'blog' => 'bai-viet',
    'uppercase' => 'Gioi-Thieu',
]);
