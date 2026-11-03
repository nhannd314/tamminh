<?php

namespace Database\Seeders;

use App\Models\Expert;
use Illuminate\Database\Seeder;

/**
 * Các chuyên gia mẫu ban đầu. Chạy lại nhiều lần không tạo trùng (khớp theo slug).
 */
class ExpertSeeder extends Seeder
{
    public function run(): void
    {
        $experts = [
            [
                'slug' => 'minh-quang', 'name' => 'Thầy Minh Quang', 'glyph' => '明', 'years_experience' => 30, 'sessions_count' => 1287, 'sort' => 1,
                'title' => 'Chuyên gia nhân tướng học · Hà Nội',
                'headline' => '30 năm đọc tướng, giữ lối luận giải truyền thống',
                'phone' => '0888 11 2626',
                'email' => 'minhquang@tamminh.vn',
                'address' => 'Số 26A ngõ 22 Thạch Bàn, Long Biên, Hà Nội',
                'working_hours' => 'Thứ 2 – Thứ 7 · 8:00 – 17:30',
                'rating' => 4.9,
                'reviews_count' => 312,
                'description' => "Thầy Minh Quang theo học nhân tướng học từ năm 1994, kế thừa phép xem Tam đình – Ngũ quan – Thập nhị cung từ dòng thầy ở Nam Định. Thầy chú trọng quan sát thần khí và sắc diện, đối chiếu với từng giai đoạn tuổi để đưa ra lời khuyên cụ thể, dễ áp dụng.\nMỗi buổi xem đều được trao đổi trực tiếp, không phán đoán chung chung. Thầy giúp người xem hiểu rõ điểm mạnh của bản thân, những giai đoạn cần thận trọng và cách điều chỉnh nơi ở, nơi làm việc cho hợp bản mệnh.",
                'specialties' => ['Nhân tướng học', 'Tam đình – Ngũ quan', 'Xem chỉ tay', 'Phong thủy nhà ở', 'Chọn ngày khai trương'],
                'services' => [
                    ['name' => 'Xem tướng trực tiếp', 'description' => 'Luận giải toàn diện khuôn mặt, thần khí sắc và vận hạn 10 năm tới.', 'duration' => '60 phút', 'price' => '800.000đ'],
                    ['name' => 'Xem chỉ tay', 'description' => 'Đọc sinh đạo, trí đạo, tâm đạo và các đường phụ trên hai bàn tay.', 'duration' => '45 phút', 'price' => '500.000đ'],
                    ['name' => 'Tư vấn phong thủy nhà ở', 'description' => 'Khảo sát hướng nhà, bố trí phòng ngủ, bàn thờ, bàn làm việc.', 'duration' => 'Tại nhà', 'price' => '2.500.000đ'],
                ],
                'reviews' => [
                    ['rating' => 5, 'name' => 'Chị Thu Hà', 'location' => 'Hà Nội', 'quote' => 'Thầy nói đúng giai đoạn công việc chững lại của tôi năm 34 tuổi, lời khuyên đổi hướng bàn làm việc rất thực tế.'],
                    ['rating' => 5, 'name' => 'Anh Quốc Bảo', 'location' => 'Hải Phòng', 'quote' => 'Buổi xem rõ ràng, không hù dọa. Thầy giải thích từng bộ vị nên tôi hiểu vì sao mình hay nóng vội.'],
                ],
                'tiktok_url' => 'https://www.tiktok.com/@thayminhquang',
                'facebook_url' => 'https://www.facebook.com/thayminhquang',
            ],
            ['slug' => 'tan-phat', 'name' => 'Thầy Tấn Phát', 'glyph' => '太', 'years_experience' => 25, 'sessions_count' => 956, 'sort' => 2],
            ['slug' => 'thien-loc', 'name' => 'Thầy Thiên Lộc', 'glyph' => '禄', 'years_experience' => 20, 'sessions_count' => 743, 'sort' => 3],
        ];

        foreach ($experts as $expert) {
            Expert::query()->updateOrCreate(['slug' => $expert['slug']], [
                'title' => 'Chuyên gia nhân tướng học',
                'description' => "Chuyên gia nhân tướng học với {$expert['years_experience']} năm kinh nghiệm, từng đồng hành cùng hàng nghìn khách hàng trong các vấn đề sự nghiệp, tình duyên và phong thủy.\nPhong cách luận giải gần gũi, đi từ nguyên lý đến lời khuyên thực tế để bạn chủ động cải vận.",
                ...$expert,
            ]);
        }
    }
}
