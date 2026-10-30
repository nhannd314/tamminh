<?php

test('home page renders every landing section', function () {
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
