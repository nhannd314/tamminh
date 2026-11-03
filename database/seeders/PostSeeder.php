<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\PostTone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Chuyên mục và các bài viết mẫu ban đầu. Chạy lại nhiều lần không tạo trùng (khớp theo slug).
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Tướng pháp', 'slug' => 'tuong-phap', 'description' => 'Đọc khuôn mặt theo nhân tướng học: ngũ quan, tam đình, thần khí sắc và thập nhị cung.', 'sort' => 1],
            ['name' => 'Tử vi', 'slug' => 'tu-vi', 'description' => 'Bát tự, nạp âm bản mệnh, vận năm và cách đọc lá số.', 'sort' => 2],
            ['name' => 'Phong thủy', 'slug' => 'phong-thuy', 'description' => 'Ứng dụng ngũ hành vào nhà ở, nơi làm việc và sinh hoạt hằng ngày.', 'sort' => 3],
        ])->mapWithKeys(fn (array $category): array => [
            $category['slug'] => PostCategory::query()->updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        foreach ($this->posts() as $post) {
            Post::query()->updateOrCreate(['slug' => $post['slug']], [
                ...Arr::except($post, 'category'),
                'post_category_id' => $categories[$post['category']]->getKey(),
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function posts(): array
    {
        return [
            [
                'category' => 'tuong-phap',
                'slug' => 'doc-khuon-mat-qua-than-khi-sac',
                'title' => 'Đọc khuôn mặt qua thần - khí - sắc: ba tầng năng lượng người xưa tin cậy',
                'excerpt' => 'Trước khi xét ngũ quan, các thầy tướng xưa thường đọc "thần" trước tiên. Bài viết giải thích ba lớp thần, khí, sắc trên khuôn mặt và cách tự quan sát.',
                'glyph' => '相',
                'tone' => PostTone::Copper,
                'author_name' => 'Thầy Minh Quang',
                'published_at' => '2026-05-12 08:00:00',
                'content' => <<<'HTML'
                    <p>Trong nhân tướng học, hình dáng ngũ quan chỉ là một phần. Người xem tướng lâu năm thường nhìn tổng thể khuôn mặt trước, theo ba tầng: <strong>thần</strong>, <strong>khí</strong> và <strong>sắc</strong>. Ba tầng này thay đổi theo sức khỏe và tâm trạng, nên phản ánh trạng thái hiện tại rõ hơn đường nét cố định.</p>
                    <h2>Thần: ánh mắt và tinh thần</h2>
                    <p>Thần thể hiện chủ yếu qua đôi mắt. Mắt có thần là mắt sáng, tròng đen tròng trắng rõ ràng, ánh nhìn ổn định, không đảo liên tục. Người xưa cho rằng thần tốt gắn với sự tỉnh táo, tập trung và ý chí bền.</p>
                    <p>Thần kém thường thấy khi mắt mờ, mi mắt nặng, ánh nhìn tản mát. Đây phần nhiều là dấu hiệu mệt mỏi, thiếu ngủ hoặc căng thẳng kéo dài, không phải điều cố định.</p>
                    <h2>Khí: sinh lực toát ra từ dáng vẻ</h2>
                    <p>Khí khó chỉ ra bằng một bộ phận cụ thể. Nó là cảm giác chung về sinh lực: nét mặt thư thái hay căng cứng, cơ mặt cân đối hay lệch, dáng ngồi vững hay co rút. Khí ổn định cho thấy người có năng lượng đều đặn, làm việc bền.</p>
                    <h2>Sắc: màu da và độ sáng</h2>
                    <p>Sắc là màu và độ sáng của da, đặc biệt ở trán, ấn đường (giữa hai lông mày) và sống mũi. Sắc hồng nhuận, sáng đều được xem là tốt. Sắc xám, tối hoặc có vùng thâm bất thường được xem là cần lưu ý.</p>
                    <blockquote>Thần, khí, sắc thay đổi theo ngày. Vì vậy người xem tướng coi đây là chỉ báo cho giai đoạn hiện tại, không phải kết luận cho cả đời.</blockquote>
                    <h2>Tự quan sát thế nào</h2>
                    <ul>
                        <li>Chụp ảnh chính diện vào buổi sáng, ánh sáng tự nhiên, không dùng bộ lọc.</li>
                        <li>So sánh ảnh ở các thời điểm khác nhau: khi nghỉ ngơi đủ và khi bận rộn kéo dài.</li>
                        <li>Chú ý vùng ấn đường và quanh mắt - đây là nơi thay đổi rõ nhất.</li>
                    </ul>
                    <p>Nếu thần, khí, sắc kém kéo dài, điều nên làm trước tiên là điều chỉnh giấc ngủ, ăn uống và nghỉ ngơi. Với các dấu hiệu sức khỏe bất thường, hãy gặp bác sĩ thay vì chỉ dựa vào tướng mặt.</p>
                    HTML,
            ],
            [
                'category' => 'tu-vi',
                'slug' => 'nam-binh-ngo-2026-van-khi-12-con-giap',
                'title' => 'Năm Bính Ngọ 2026: vận khí 12 con giáp và những điều cần lưu ý',
                'excerpt' => 'Năm Bính Ngọ mang hành Thủy theo nạp âm, can Bính và chi Ngọ thuộc Hỏa. Bài viết điểm qua quan hệ của năm với từng tuổi theo tam hợp, lục hợp, xung và hại.',
                'glyph' => '紫',
                'tone' => PostTone::Navy,
                'author_name' => 'Thầy Huyền Lâm',
                'published_at' => '2026-05-08 08:00:00',
                'content' => <<<'HTML'
                    <p>Năm 2026 là năm <strong>Bính Ngọ</strong>. Can Bính và chi Ngọ đều thuộc hành Hỏa; nạp âm của năm là Thiên Hà Thủy. Trong tử vi, quan hệ giữa chi của năm và chi tuổi của mỗi người là một trong những căn cứ để xem năm thuận hay cần thận trọng.</p>
                    <h2>Các quan hệ thường được xét</h2>
                    <ul>
                        <li><strong>Năm tuổi (trùng chi):</strong> người tuổi Ngọ. Theo quan niệm dân gian là năm "phạm Thái tuế", nên làm việc chắc chắn, hạn chế quyết định vội.</li>
                        <li><strong>Lục xung:</strong> Tý xung Ngọ. Người tuổi Tý dễ gặp thay đổi, nên chuẩn bị kế hoạch dự phòng.</li>
                        <li><strong>Lục hại:</strong> Sửu hại Ngọ. Người tuổi Sửu nên chú ý các mối quan hệ hợp tác.</li>
                        <li><strong>Tam hợp:</strong> Dần - Ngọ - Tuất. Người tuổi Dần và Tuất được xem là có năm thuận lợi hơn.</li>
                        <li><strong>Lục hợp:</strong> Ngọ hợp Mùi. Người tuổi Mùi thuận cho việc hợp tác, kết giao.</li>
                    </ul>
                    <h2>Đọc những quan hệ này thế nào cho đúng</h2>
                    <p>Quan hệ giữa chi năm và chi tuổi chỉ là một yếu tố. Một lá số đầy đủ còn xét trụ tháng, ngày, giờ sinh, ngũ hành vượng suy và đại vận. Hai người cùng tuổi Tý vẫn có thể có năm 2026 rất khác nhau.</p>
                    <blockquote>"Xung" không có nghĩa là xui. Trong nhiều trường hợp, năm xung là năm có thay đổi - thay đổi tốt hay xấu phụ thuộc vào việc chuẩn bị.</blockquote>
                    <h2>Gợi ý chung cho năm Hỏa vượng</h2>
                    <ol>
                        <li>Giữ nhịp làm việc đều, tránh dồn việc rồi kiệt sức.</li>
                        <li>Với quyết định tài chính lớn, đặt thời hạn suy nghĩ thay vì quyết ngay.</li>
                        <li>Người có bản mệnh Kim hoặc Hỏa vượng trong bát tự nên chú ý cân bằng, nghỉ ngơi và kiểm soát cảm xúc.</li>
                    </ol>
                    <p>Muốn biết năm 2026 ảnh hưởng thế nào đến riêng mình, cách tốt nhất là xem trên lá số cá nhân thay vì chỉ dựa vào con giáp.</p>
                    HTML,
            ],
            [
                'category' => 'phong-thuy',
                'slug' => 'bai-tri-phong-lam-viec-theo-ban-menh',
                'title' => 'Bài trí phòng làm việc theo bản mệnh - cách hút tài lộc không cần linh vật',
                'excerpt' => 'Ngũ hành bản mệnh gợi ý hướng bàn, màu sắc, ánh sáng và cây xanh. Hướng dẫn tự kiểm tra phòng làm việc trong 10 phút, không cần mua vật phẩm đắt tiền.',
                'glyph' => '風',
                'tone' => PostTone::Cream,
                'author_name' => 'Thầy An Khang',
                'published_at' => '2026-05-02 08:00:00',
                'content' => <<<'HTML'
                    <p>Phong thủy nơi làm việc không bắt đầu từ linh vật. Nó bắt đầu từ những điều cơ bản: chỗ ngồi có điểm tựa, ánh sáng đủ, không gian gọn gàng. Sau đó mới đến màu sắc và hướng theo bản mệnh.</p>
                    <h2>Bước 1: chỗ ngồi</h2>
                    <ul>
                        <li>Lưng tựa vào tường hoặc vách vững, tránh ngồi quay lưng ra cửa.</li>
                        <li>Nhìn thấy được cửa ra vào mà không phải ngồi thẳng hàng với cửa.</li>
                        <li>Tránh ngồi ngay dưới dầm ngang hoặc góc nhọn chĩa vào.</li>
                    </ul>
                    <h2>Bước 2: ánh sáng và không khí</h2>
                    <p>Ánh sáng tự nhiên nên chiếu từ bên cạnh, không chiếu thẳng vào mắt hay màn hình. Thêm một cây xanh lá tròn giúp không gian dịu hơn. Dọn bỏ giấy tờ, dây cáp lộn xộn trên bàn.</p>
                    <h2>Bước 3: màu sắc theo bản mệnh</h2>
                    <table>
                        <tr><th>Bản mệnh</th><th>Màu hợp</th><th>Màu tương sinh</th></tr>
                        <tr><td>Kim</td><td>Trắng, xám, ánh kim</td><td>Vàng, nâu đất (Thổ sinh Kim)</td></tr>
                        <tr><td>Mộc</td><td>Xanh lá</td><td>Xanh dương, đen (Thủy sinh Mộc)</td></tr>
                        <tr><td>Thủy</td><td>Xanh dương, đen</td><td>Trắng, xám (Kim sinh Thủy)</td></tr>
                        <tr><td>Hỏa</td><td>Đỏ, cam, hồng</td><td>Xanh lá (Mộc sinh Hỏa)</td></tr>
                        <tr><td>Thổ</td><td>Vàng, nâu đất</td><td>Đỏ, cam (Hỏa sinh Thổ)</td></tr>
                    </table>
                    <p>Không cần sơn lại cả phòng. Một tấm lót bàn, khung ảnh, bìa sổ hay chậu cây đúng màu là đủ.</p>
                    <h2>Bước 4: hướng bàn</h2>
                    <p>Theo ngũ hành, mỗi hướng ứng với một hành: Đông và Đông Nam thuộc Mộc, Nam thuộc Hỏa, Tây và Tây Bắc thuộc Kim, Bắc thuộc Thủy, Đông Bắc và Tây Nam thuộc Thổ. Nếu được chọn, hãy quay mặt về hướng thuộc hành bản mệnh hoặc hành sinh ra bản mệnh. Nếu không đổi được hướng, ưu tiên bước 1 và bước 2 - chúng quan trọng hơn.</p>
                    <blockquote>Một chỗ ngồi vững, sáng và gọn gàng có tác dụng thực tế với hiệu quả làm việc, dù bạn có tin vào phong thủy hay không.</blockquote>
                    HTML,
            ],
        ];
    }
}
