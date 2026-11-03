<?php

use App\Models\Expert;
use App\Models\FaceAnalysis;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home page renders every landing section', function () {
    Post::factory()->create();
    Expert::factory()->create();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeText('Nhập thông tin của bạn')
        ->assertSeeText('Ngũ hành tướng pháp')
        ->assertSeeText('Thập nhị cung mệnh')
        ->assertSeeText('Xem trực tiếp với thầy tướng số')
        ->assertSeeText('Đặt lịch xem chỉ tay')
        ->assertSeeText('Khuôn mặt đã luận giải')
        ->assertSeeText('Bài viết & tư liệu')
        ->assertSeeText('Những câu hỏi')
        ->assertSeeText('Bảo mật thông tin')
        ->assertSeeText('Bản tin Tâm Minh');
});

test('home page renders data driven lists', function () {
    Expert::factory()->create(['name' => 'Thầy Minh Quang', 'sessions_count' => 1287]);

    $response = $this->get(route('home'));

    $response->assertSeeText('Thầy Minh Quang')
        ->assertSeeText('1.287')
        ->assertSeeText('Tật ách cung')
        ->assertSeeText('Xem tướng có phải mê tín không?');
});

test('hero form asks for birth hour and minute', function () {
    $response = $this->get(route('home'));

    $response->assertSeeText('Giờ sinh')
        ->assertSee('name="birth_hour"', false)
        ->assertSee('name="birth_minute"', false)
        ->assertSee('<option value="23" >23 giờ</option>', false)
        ->assertSee('<option value="59" >59 phút</option>', false);
});

test('hero form requires agreeing to the terms', function () {
    $this->get(route('home'))
        ->assertSee('name="agree_terms" value="1" class="sr-only"  required', false)
        ->assertSeeText('Tôi đồng ý với');
});

test('design system page renders components', function () {
    $response = $this->get(route('design-system'));

    $response->assertOk()
        ->assertSeeText('Buttons')
        ->assertSeeText('Form controls')
        ->assertSee('noindex', false);
});

test('home page links each expert to their profile without a role line or list button', function () {
    $expert = Expert::factory()->create(['slug' => 'minh-quang']);
    Expert::factory()->inactive()->create(['name' => 'Thầy Đã Ẩn']);

    $this->get(route('home'))
        ->assertSee(route('experts.show', $expert), false)
        ->assertDontSeeText('Xem tất cả chuyên gia')
        ->assertDontSeeText('Chuyên gia tướng số')
        ->assertDontSeeText('Thầy Đã Ẩn');
});

test('home page reads testimonials and faqs from the site config', function () {
    config([
        'site.testimonials' => [
            ['rating' => 5, 'quote' => 'Lời cảm nhận cấu hình.', 'name' => 'Khách Cấu Hình', 'role' => 'Hà Nội', 'service' => 'Xem chỉ tay', 'avatar' => 'K', 'tone' => 'navy'],
        ],
        'site.faqs' => [
            ['question' => 'Câu hỏi cấu hình?', 'answer' => 'Ảnh xóa sau :portrait_days ngày.'],
        ],
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Lời cảm nhận cấu hình.')
        ->assertSeeText('Khách Cấu Hình')
        ->assertSeeText('Câu hỏi cấu hình?')
        ->assertSeeText('Ảnh xóa sau '.FaceAnalysis::PORTRAIT_RETENTION_DAYS.' ngày.')
        ->assertDontSeeText('Nguyễn Thanh Linh');
});
