<?php

/**
 * Thông tin doanh nghiệp hiển thị ở footer, trang Liên hệ và các trang chính sách,
 * cùng cảm nhận khách hàng và câu hỏi thường gặp ngoài trang chủ.
 */
return [

    'company' => env('SITE_COMPANY', 'Tâm Minh JSC'),

    'tax_code' => env('SITE_TAX_CODE', '0108234567'),

    /*
    |--------------------------------------------------------------------------
    | Kênh liên hệ
    |--------------------------------------------------------------------------
    |
    | Giá trị mặc định; admin sửa được ở trang Cấu hình > Liên hệ (lưu bảng settings, ưu tiên hơn).
    | Luôn đọc qua App\Support\ShopContact thay vì config() trực tiếp.
    |
    */

    // Hotline gọi điện.
    'phone' => env('SITE_PHONE', '0888-11-2626'),

    // Số điện thoại đăng ký Zalo của cửa hàng (có thể khác hotline).
    'zalo' => env('SITE_ZALO', '0888-11-2626'),

    'email' => env('SITE_EMAIL', 'contact@tamminh.vn'),

    'address' => env('SITE_ADDRESS', 'Số 26A ngõ 22 Thạch Bàn, Long Biên, Hà Nội'),

    'hours' => env('SITE_HOURS', '8:00 - 21:00, tất cả các ngày trong tuần'),

    // Trang mạng xã hội ở footer; để trống thì ẩn biểu tượng. Nút Zalo lấy theo số Zalo ở trên.
    'facebook' => env('SITE_FACEBOOK_URL', ''),

    'youtube' => env('SITE_YOUTUBE_URL', ''),

    'tiktok' => env('SITE_TIKTOK_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Cảm nhận của khách hàng
    |--------------------------------------------------------------------------
    |
    | `avatar` là chữ cái hiện trong ô đại diện, `tone` là màu ô: copper | navy.
    |
    */

    'testimonials' => [
        [
            'rating' => 5,
            'quote' => 'Báo cáo của Tâm Minh chi tiết đến từng cung mệnh, đọc xong mình thấy hiểu chính mình hơn - kiểu tâm lý học chứ không phải bói toán mê tín. Câu chuyện rất riêng tư, không có cảm giác "văn mẫu".',
            'name' => 'Nguyễn Thanh Linh',
            'role' => 'Nhà thiết kế · Hà Nội',
            'service' => 'Luận giải tử vi trọn đời',
            'avatar' => 'L',
            'tone' => 'copper',
        ],
        [
            'rating' => 5,
            'quote' => 'Công nghệ phân tích khuôn mặt nhận diện tướng mạo khá chính xác, nhưng điều mình ấn tượng là phần Thầy luận giải bằng văn phong gần gũi. Mình hỏi gì cũng được trả lời cặn kẽ, không phải kiểu chốt sale dịch vụ.',
            'name' => 'Trần Minh Tuấn',
            'role' => 'Giám đốc kinh doanh · TP.HCM',
            'service' => 'Xem tướng số cùng Thầy Minh Quang',
            'avatar' => 'T',
            'tone' => 'navy',
        ],
        [
            'rating' => 5,
            'quote' => 'Cô từng đi xem ở nhiều nơi nhưng nội dung na ná nhau. Tâm Minh hỏi rất kỹ về hoàn cảnh, bài luận viết riêng cho mình. Phần phong thủy nhà ở thực sự hữu ích, đã áp dụng và thấy gia đạo êm ấm hơn.',
            'name' => 'Lê Thị Hương',
            'role' => 'Chủ doanh nghiệp · Đà Nẵng',
            'service' => 'Phong thủy nhà ở',
            'avatar' => 'H',
            'tone' => 'copper',
        ],
        [
            'rating' => 5,
            'quote' => 'Mình hoài nghi mấy kiểu xem tướng online, nhưng Tâm Minh khác - họ giải thích "tại sao" chứ không phán xét. Phần lời khuyên cải vận rất thực tế: nên làm gì, tránh gì, không có vẽ vời.',
            'name' => 'Phạm Đức Anh',
            'role' => 'Lập trình viên · Hà Nội',
            'service' => 'Luận giải nhân tướng',
            'avatar' => 'Đ',
            'tone' => 'navy',
        ],
        [
            'rating' => 5,
            'quote' => 'Thầy Huyền Lâm tư vấn cho mình về sự nghiệp và hôn nhân rất tâm huyết. Mỗi lần nhắn tin Thầy đều phản hồi nhanh, lời khuyên đúng trọng tâm. Đáng tiền và đáng trân quý.',
            'name' => 'Vũ Thu Hoa',
            'role' => 'Giáo viên · Cần Thơ',
            'service' => 'Tư vấn 1-1 với Thầy Huyền Lâm',
            'avatar' => 'H',
            'tone' => 'copper',
        ],
        [
            'rating' => 5,
            'quote' => 'Thầy xem chỉ tay cho mình rất kỹ - đường sinh đạo, trí đạo, tâm đạo đều giải thích cặn kẽ, nhiều điều nói trúng những chuyện mình đã trải qua. Mức độ chuyên môn ở đây thực sự nghiêm túc.',
            'name' => 'Hoàng Văn Khải',
            'role' => 'Doanh nhân · TP.HCM',
            'service' => 'Xem chỉ tay với chuyên gia',
            'avatar' => 'K',
            'tone' => 'navy',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Câu hỏi thường gặp
    |--------------------------------------------------------------------------
    |
    | Trong câu trả lời, `:portrait_days` được thay bằng số ngày lưu ảnh chân dung
    | (FaceAnalysis::PORTRAIT_RETENTION_DAYS) để luôn khớp với thực tế.
    |
    */

    'faqs' => [
        [
            'question' => 'Tâm Minh là dịch vụ như thế nào?',
            'answer' => 'Tâm Minh là nền tảng xem tướng số và chỉ tay, kết hợp công nghệ phân tích khuôn mặt với đội ngũ chuyên gia nhân tướng hàng đầu Việt Nam. Bạn có thể nhận báo cáo luận giải chi tiết hoặc đặt tư vấn 1-1 với các Thầy có 18-30 năm kinh nghiệm.',
        ],
        [
            'question' => 'Công nghệ phân tích khuôn mặt chính xác đến đâu?',
            'answer' => 'Hệ thống đo đạc và tái tạo chính xác hàng trăm điểm mốc trên khuôn mặt - tỷ lệ tam đình, hình dáng ngũ quan, đường nét và khí sắc - rồi đối chiếu với tướng pháp truyền thống. Phần luận giải sâu về vận hạn, tài lộc, tình duyên do Thầy trực tiếp phụ trách.',
        ],
        [
            'question' => 'Cần chuẩn bị ảnh như thế nào?',
            'answer' => 'Chụp chính diện, đủ sáng, không đeo kính, không dùng filter làm mịn da. Ảnh rõ nét giúp công nghệ tái tạo khuôn mặt chính xác hơn, từ đó kết quả luận giải sát với tướng mạo thật của bạn.',
        ],
        [
            'question' => 'Thông tin cá nhân và ảnh của tôi được bảo mật ra sao?',
            'answer' => 'Ảnh chân dung được lưu riêng tư, chỉ dùng để luận giải cho chính bạn và tự động xóa sau :portrait_days ngày. Kết quả luận giải đã viết vẫn được giữ để bạn xem lại. Bạn có thể yêu cầu xóa toàn bộ dữ liệu bất cứ lúc nào.',
        ],
        [
            'question' => 'Báo cáo được gửi trong bao lâu?',
            'answer' => 'Báo cáo phân tích khuôn mặt: vài phút. Báo cáo có Thầy luận giải: 24-48 giờ. Tư vấn 1-1 trực tiếp: đặt lịch trong vòng 24 giờ. Mức độ chuyên sâu càng cao thì thời gian luận giải càng kỹ.',
        ],
        [
            'question' => 'Chi phí mỗi loại dịch vụ là bao nhiêu?',
            'answer' => 'Xem tướng cơ bản: miễn phí. Luận giải nhân tướng chuyên sâu: 199.000đ. Luận giải có Thầy phản biện: từ 599.000đ. Xem chỉ tay / tư vấn 1-1 với Thầy: từ 1.200.000đ/giờ tùy chuyên gia. Tất cả minh bạch trước khi thanh toán.',
        ],
        [
            'question' => 'Xem tướng có phải mê tín không?',
            'answer' => 'Tâm Minh không xem tướng số như "định mệnh không thể đổi". Chúng tôi coi đó là công cụ tự nhận thức - giúp bạn hiểu xu hướng tính cách, điểm mạnh, điểm yếu - để chủ động cải vận, chứ không phán xét.',
        ],
    ],

];
