<?php

/**
 * Dữ liệu giả cho thông báo hoạt động (social proof) chạy ngẫu nhiên qua `php artisan activity:fake`.
 *
 * Mỗi mẫu trong `templates` có `{name}`, `{city}`, `{product}`, `{package}` được thay bằng giá trị ngẫu nhiên bên dưới.
 * Muốn thêm/bớt nội dung chỉ cần sửa file này.
 */
return [
    'names' => [
        'Minh Anh', 'Thu Hà', 'Quốc Bảo', 'Ngọc Lan', 'Hoàng Nam', 'Phương Linh', 'Đức Huy', 'Thanh Tâm',
        'Mai Chi', 'Văn Tùng', 'Bích Ngọc', 'Gia Hân', 'Tuấn Kiệt', 'Hải Yến', 'Khánh Vy', 'Thảo Nguyên',
        'Anh Tuấn', 'Diệu Linh', 'Trọng Nghĩa', 'Kim Oanh', 'Bảo Châu', 'Việt Hưng', 'Lan Hương', 'Đăng Khoa',
    ],

    'cities' => [
        'Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ', 'Huế', 'Nha Trang', 'Đà Lạt',
        'Bình Dương', 'Biên Hòa', 'Vũng Tàu', 'Quảng Ninh', 'Nghệ An', 'Thanh Hóa',
    ],

    'products' => [
        'Vòng tay đá thạch anh tím', 'Vòng tay mắt hổ', 'Tỳ hưu phong thủy', 'Mặt dây chuyền Phật Bản Mệnh',
        'Vòng tay obsidian', 'Cây tài lộc đá', 'Nhẫn đá moonstone', 'Bộ vòng tay ngũ hành',
    ],

    'packages' => [
        'gói cơ bản', 'gói chuyên sâu', 'gói trọn đời',
    ],

    /**
     * kind quyết định biểu tượng hiển thị; weight càng lớn càng hay xuất hiện.
     */
    'templates' => [
        ['kind' => 'analysis', 'weight' => 4, 'message' => '{name} ({city}) vừa đăng ký xem tướng luận giải'],
        ['kind' => 'unlock', 'weight' => 2, 'message' => '{name} vừa mở khoá luận giải đầy đủ'],
        ['kind' => 'palm', 'weight' => 2, 'message' => '{name} vừa đặt lịch xem chỉ tay ({package})'],
        ['kind' => 'cart', 'weight' => 4, 'message' => '{name} vừa thêm “{product}” vào giỏ hàng'],
        ['kind' => 'order', 'weight' => 3, 'message' => '{name} ({city}) vừa đặt hàng “{product}”'],
        ['kind' => 'paid', 'weight' => 2, 'message' => '{name} vừa thanh toán thành công đơn hàng'],
    ],
];
