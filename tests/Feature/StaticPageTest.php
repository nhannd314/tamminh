<?php

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a published page is shown at its slug', function () {
    $page = Page::factory()->create(['title' => 'Điều khoản dịch vụ', 'slug' => 'dieu-khoan-dich-vu', 'content' => '<p>Nội dung điều khoản.</p>']);

    $this->get('/dieu-khoan-dich-vu')
        ->assertOk()
        ->assertSeeText('Điều khoản dịch vụ')
        ->assertSeeText('Nội dung điều khoản.')
        ->assertSee('<link rel="canonical" href="'.route('pages.show', $page).'">', false);
});

test('hidden and unknown pages return 404', function () {
    Page::factory()->hidden()->create(['slug' => 'trang-an']);

    $this->get('/trang-an')->assertNotFound();
    $this->get('/khong-ton-tai')->assertNotFound();
});

test('the page route does not catch the admin panel', function () {
    $this->get('/admin')->assertRedirect();
});

test('the contact page shows the contact details from the site config', function () {
    config(['site.phone' => '0900-000-111', 'site.email' => 'hotro@example.test']);
    Page::factory()->create(['slug' => 'lien-he', 'title' => 'Liên hệ']);

    $this->get('/lien-he')
        ->assertOk()
        ->assertSeeText('0900-000-111')
        ->assertSeeText('hotro@example.test');
});

test('the page seeder creates every linked page and can run twice', function () {
    $this->seed(PageSeeder::class);
    $this->seed(PageSeeder::class);

    expect(Page::query()->pluck('slug')->all())->toEqualCanonicalizing([
        'gioi-thieu', 'dieu-khoan-dich-vu', 'chinh-sach-bao-mat', 'chinh-sach-cookie', 'huong-dan-su-dung', 'lien-he',
    ]);

    $this->get(route('home'))
        ->assertSee(route('pages.show', 'dieu-khoan-dich-vu'), false)
        ->assertSee(route('pages.show', 'chinh-sach-bao-mat'), false)
        ->assertSee(route('pages.show', 'lien-he'), false);

    $this->get('/chinh-sach-bao-mat')
        ->assertOk()
        ->assertSeeText('Google (Gemini API)')
        ->assertSeeText(config('site.email'));
});
