<?php

namespace Database\Seeders;

use App\Models\FaceAnalysis;
use App\Models\Page;
use App\Support\ShopContact;
use Illuminate\Database\Seeder;

/**
 * Các trang tĩnh mặc định (giới thiệu, điều khoản, bảo mật, cookie, hướng dẫn, liên hệ).
 * Chạy lại nhiều lần không tạo trùng; trang đã có thì được ghi đè bằng nội dung mặc định.
 *
 * Nội dung pháp lý là bản nháp mô tả đúng cách hệ thống đang hoạt động - cần người có chuyên môn rà soát trước khi chạy thật.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $slug => $page) {
            Page::query()->updateOrCreate(['slug' => $slug], [...$page, 'is_published' => true]);
        }
    }

    /**
     * @return array<string, array{title: string, summary: string, meta_description: string, content: string}>
     */
    private function pages(): array
    {
        $company = config('site.company');
        $email = ShopContact::email();
        $phone = ShopContact::hotline();
        $address = ShopContact::address();
        $hours = ShopContact::hours();
        $taxCode = config('site.tax_code');
        $retentionDays = FaceAnalysis::PORTRAIT_RETENTION_DAYS;

        return [
            'gioi-thieu' => [
                'title' => 'Giới thiệu Tâm Minh',
                'summary' => 'Nền tảng xem tướng khuôn mặt, kết hợp đo đạc từ ảnh chân dung với tri thức nhân tướng học phương Đông.',
                'meta_description' => 'Tâm Minh là nền tảng xem tướng khuôn mặt trực tuyến: đo tỷ lệ khuôn mặt từ ảnh, kết hợp bát tự và nhân tướng học phương Đông.',
                'content' => <<<HTML
                    <p>Tâm Minh là nền tảng xem tướng trực tuyến. Bạn tải lên một ảnh chân dung và nhập ngày giờ sinh. Hệ thống đo các tỷ lệ trên khuôn mặt, lập lá số bát tự, rồi soạn bản luận giải theo các phương pháp nhân tướng học phương Đông.</p>
                    <h2>Chúng tôi làm gì</h2>
                    <ul>
                        <li><strong>Đo đạc khuôn mặt:</strong> nhận diện khuôn mặt, cắt vùng mặt và đo tỷ lệ tam đình (thượng, trung, hạ đình) trực tiếp từ ảnh.</li>
                        <li><strong>Lập lá số:</strong> tính bát tự (năm, tháng, ngày, giờ), nạp âm bản mệnh, cục mệnh và thân cư từ ngày giờ sinh.</li>
                        <li><strong>Luận giải:</strong> soạn nội dung theo từng phần - ngũ hành tướng pháp, thần khí sắc, tam đình, ngũ quan, thập nhị cung, phong thủy và lời khuyên cải vận.</li>
                    </ul>
                    <h2>Cách chúng tôi nhìn nhận tướng số</h2>
                    <p>Chúng tôi xem nhân tướng học là một hệ thống tri thức truyền thống giúp mỗi người suy ngẫm về tính cách, điểm mạnh và điều cần lưu ý của bản thân. Kết quả luận giải mang tính tham khảo, không phải lời phán định về tương lai, và không thay thế cho tư vấn y tế, pháp lý hay tài chính.</p>
                    <h2>Thông tin doanh nghiệp</h2>
                    <p>{$company} - Mã số thuế {$taxCode}.<br>Địa chỉ: {$address}.<br>Email: {$email} - Hotline: {$phone}.</p>
                    HTML,
            ],

            'dieu-khoan-dich-vu' => [
                'title' => 'Điều khoản dịch vụ',
                'summary' => 'Các điều kiện áp dụng khi bạn sử dụng dịch vụ xem tướng và luận giải của Tâm Minh.',
                'meta_description' => 'Điều khoản sử dụng dịch vụ xem tướng khuôn mặt, thanh toán và quyền, nghĩa vụ của người dùng tại Tâm Minh.',
                'content' => <<<HTML
                    <p>Khi gửi ảnh và thông tin để xem tướng tại Tâm Minh, bạn đồng ý với các điều khoản dưới đây. Vui lòng đọc kỹ trước khi sử dụng dịch vụ.</p>
                    <h2>1. Dịch vụ</h2>
                    <p>Tâm Minh cung cấp bản luận giải nhân tướng học dựa trên ảnh chân dung và ngày giờ sinh bạn cung cấp. Bản luận giải gồm nhiều phần: một số phần được xem miễn phí, các phần chuyên sâu cần thanh toán để mở khóa theo bảng giá hiển thị trên trang kết quả.</p>
                    <p>Nội dung luận giải được tạo tự động bằng hệ thống phân tích của Tâm Minh, có sử dụng mô hình trí tuệ nhân tạo của bên thứ ba. Nội dung mang tính tham khảo, không phải lời khẳng định về sự việc trong tương lai, và không thay thế cho tư vấn y tế, tâm lý, pháp lý hoặc tài chính.</p>
                    <h2>2. Điều kiện sử dụng</h2>
                    <ul>
                        <li>Bạn từ đủ 18 tuổi, hoặc có sự đồng ý của cha mẹ hoặc người giám hộ.</li>
                        <li>Ảnh bạn tải lên là ảnh của chính bạn, hoặc bạn đã được người trong ảnh đồng ý cho sử dụng vào mục đích xem tướng.</li>
                        <li>Không tải lên ảnh có nội dung vi phạm pháp luật, xúc phạm hoặc xâm phạm quyền của người khác.</li>
                        <li>Không dùng công cụ tự động để gửi yêu cầu hàng loạt, dò quét hoặc làm gián đoạn hệ thống.</li>
                    </ul>
                    <h2>3. Kết quả và đường dẫn xem kết quả</h2>
                    <p>Mỗi lượt xem tướng có một đường dẫn riêng chứa mã bí mật. Bất kỳ ai có đường dẫn này đều xem được kết quả, vì vậy bạn nên giữ đường dẫn cẩn thận và chỉ chia sẻ khi muốn. Tâm Minh không chịu trách nhiệm khi đường dẫn bị lộ do bạn chia sẻ.</p>
                    <h2>4. Giá và thanh toán</h2>
                    <ul>
                        <li>Giá từng phần và giá trọn gói được hiển thị trước khi bạn thanh toán. Giá trọn gói đã trừ phần bạn mua lẻ trước đó.</li>
                        <li>Thanh toán bằng chuyển khoản ngân hàng qua mã QR. Bạn cần giữ nguyên số tiền và nội dung chuyển khoản để hệ thống tự động đối soát.</li>
                        <li>Nội dung được mở khóa tự động sau khi hệ thống nhận được tiền, thường trong 1-2 phút. Nếu sau 30 phút vẫn chưa mở khóa, vui lòng liên hệ kèm ảnh chụp giao dịch.</li>
                        <li>Chuyển thiếu tiền hoặc sai nội dung có thể khiến hệ thống không đối soát được; chúng tôi sẽ hỗ trợ xử lý thủ công khi bạn liên hệ.</li>
                    </ul>
                    <h2>5. Quyền sở hữu nội dung</h2>
                    <p>Bạn được sử dụng bản luận giải của mình cho mục đích cá nhân, bao gồm lưu, in và chia sẻ. Giao diện, nội dung bài viết, hình ảnh và mã nguồn của website thuộc quyền sở hữu của {$company}, không được sao chép cho mục đích thương mại khi chưa có sự đồng ý bằng văn bản.</p>
                    <h2>6. Giới hạn trách nhiệm</h2>
                    <p>Tâm Minh không chịu trách nhiệm về các quyết định bạn đưa ra dựa trên nội dung luận giải. Chúng tôi nỗ lực duy trì dịch vụ ổn định, nhưng không cam kết dịch vụ không bao giờ gián đoạn, đặc biệt khi bảo trì hoặc khi dịch vụ của bên thứ ba gặp sự cố.</p>
                    <h2>7. Dữ liệu cá nhân</h2>
                    <p>Việc thu thập và xử lý ảnh, thông tin cá nhân được thực hiện theo <a href="/chinh-sach-bao-mat">Chính sách bảo mật</a>.</p>
                    <h2>8. Thay đổi điều khoản</h2>
                    <p>Chúng tôi có thể cập nhật điều khoản này. Ngày cập nhật được ghi ở đầu trang. Việc bạn tiếp tục sử dụng dịch vụ sau khi điều khoản thay đổi đồng nghĩa với việc bạn chấp nhận nội dung mới.</p>
                    <h2>9. Liên hệ</h2>
                    <p>Mọi thắc mắc về điều khoản, vui lòng liên hệ {$email} hoặc {$phone}.</p>
                    HTML,
            ],

            'chinh-sach-bao-mat' => [
                'title' => 'Chính sách bảo mật',
                'summary' => 'Tâm Minh thu thập dữ liệu gì, dùng vào việc gì, chia sẻ với ai và bạn có những quyền gì với dữ liệu của mình.',
                'meta_description' => 'Chính sách bảo mật của Tâm Minh: cách thu thập, sử dụng, lưu trữ và bảo vệ ảnh chân dung cùng thông tin cá nhân của bạn.',
                'content' => <<<HTML
                    <p>Ảnh chân dung là dữ liệu cá nhân nhạy cảm. Chính sách này giải thích rõ Tâm Minh xử lý ảnh và thông tin của bạn như thế nào, để bạn quyết định có sử dụng dịch vụ hay không.</p>
                    <h2>1. Dữ liệu chúng tôi thu thập</h2>
                    <ul>
                        <li><strong>Thông tin bạn nhập:</strong> họ tên, giới tính, ngày sinh và giờ sinh (nếu có).</li>
                        <li><strong>Ảnh chân dung:</strong> ảnh gốc bạn tải lên và ảnh vùng khuôn mặt được cắt ra từ ảnh đó.</li>
                        <li><strong>Số đo khuôn mặt:</strong> vị trí khuôn mặt và tỷ lệ tam đình đo được từ ảnh.</li>
                        <li><strong>Thông tin thanh toán:</strong> mã đơn hàng, số tiền, mã giao dịch và nội dung chuyển khoản do ngân hàng gửi về. Chúng tôi không thu thập số thẻ hay mật khẩu ngân hàng.</li>
                        <li><strong>Thông tin kỹ thuật:</strong> địa chỉ IP và thời điểm bạn đồng ý điều khoản, dùng để chống lạm dụng và làm bằng chứng về sự đồng ý.</li>
                    </ul>
                    <h2>2. Mục đích sử dụng</h2>
                    <ul>
                        <li>Đo đạc khuôn mặt, lập lá số và soạn bản luận giải cho chính bạn.</li>
                        <li>Đối soát thanh toán và mở khóa nội dung đã mua.</li>
                        <li>Hỗ trợ khi bạn liên hệ và xử lý khiếu nại.</li>
                        <li>Phát hiện và ngăn chặn hành vi lạm dụng hệ thống.</li>
                    </ul>
                    <p>Chúng tôi không dùng ảnh của bạn cho quảng cáo, không bán dữ liệu và không dùng ảnh để nhận dạng danh tính của bạn.</p>
                    <h2>3. Chia sẻ dữ liệu với bên thứ ba</h2>
                    <p>Để cung cấp dịch vụ, một số dữ liệu được chuyển cho các đối tác xử lý dưới đây, chỉ trong phạm vi cần thiết:</p>
                    <ul>
                        <li><strong>Google (Gemini API):</strong> nhận ảnh vùng khuôn mặt (hoặc ảnh gốc khi không cắt được vùng mặt), giới tính, ngày giờ sinh và lá số để soạn nội dung luận giải. Họ tên của bạn không được gửi đi. Máy chủ của đối tác này có thể đặt ngoài lãnh thổ Việt Nam.</li>
                        <li><strong>SePay và ngân hàng:</strong> nhận và gửi lại thông tin giao dịch chuyển khoản để đối soát thanh toán.</li>
                    </ul>
                    <p>Ngoài ra, chúng tôi chỉ cung cấp dữ liệu cho cơ quan nhà nước có thẩm quyền khi có yêu cầu theo quy định của pháp luật.</p>
                    <h2>4. Lưu trữ và bảo vệ</h2>
                    <ul>
                        <li>Ảnh được lưu ở vùng lưu trữ riêng tư trên máy chủ, không công khai. Ảnh chỉ hiển thị qua đường dẫn kết quả có mã bí mật của bạn.</li>
                        <li>Ảnh bạn đã chọn nhưng không gửi đi sẽ bị xóa khi bạn chọn ảnh khác.</li>
                        <li>Ảnh chân dung (ảnh gốc và ảnh vùng khuôn mặt) được tự động xóa sau {$retentionDays} ngày kể từ khi bạn gửi. Sau thời điểm này, các phần luận giải cần đến ảnh sẽ không mở khóa thêm được.</li>
                        <li>Thông tin bạn nhập, số đo khuôn mặt và nội dung luận giải đã viết được giữ để bạn xem lại kết quả, cho đến khi bạn yêu cầu xóa.</li>
                        <li>Kết nối tới website được mã hóa; quyền truy cập dữ liệu phía quản trị được giới hạn cho người có nhiệm vụ.</li>
                    </ul>
                    <h2>5. Quyền của bạn</h2>
                    <p>Theo pháp luật Việt Nam về bảo vệ dữ liệu cá nhân, bạn có quyền:</p>
                    <ul>
                        <li>Được biết về việc xử lý dữ liệu và yêu cầu xem dữ liệu chúng tôi đang lưu về bạn.</li>
                        <li>Yêu cầu chỉnh sửa thông tin không chính xác.</li>
                        <li>Rút lại sự đồng ý và yêu cầu xóa ảnh cùng toàn bộ kết quả luận giải. Sau khi xóa, đường dẫn kết quả sẽ không còn sử dụng được.</li>
                        <li>Khiếu nại nếu cho rằng dữ liệu của mình bị xử lý không đúng.</li>
                    </ul>
                    <p>Để thực hiện các quyền này, hãy gửi email tới {$email} kèm đường dẫn kết quả của bạn. Chúng tôi phản hồi trong vòng 72 giờ làm việc.</p>
                    <h2>6. Cookie</h2>
                    <p>Website chỉ dùng cookie cần thiết cho hoạt động. Chi tiết xem tại <a href="/chinh-sach-cookie">Chính sách cookie</a>.</p>
                    <h2>7. Liên hệ</h2>
                    <p>{$company} - {$address}.<br>Email: {$email} - Hotline: {$phone}.</p>
                    HTML,
            ],

            'chinh-sach-cookie' => [
                'title' => 'Chính sách cookie',
                'summary' => 'Website Tâm Minh dùng những cookie nào và vì sao.',
                'meta_description' => 'Chính sách cookie của Tâm Minh: website chỉ dùng cookie cần thiết cho phiên làm việc và bảo mật biểu mẫu.',
                'content' => <<<'HTML'
                    <p>Cookie là tệp nhỏ được trình duyệt lưu lại khi bạn truy cập website. Tâm Minh hiện chỉ dùng cookie cần thiết để website hoạt động.</p>
                    <h2>Cookie chúng tôi sử dụng</h2>
                    <table>
                        <tr><th>Cookie</th><th>Mục đích</th><th>Thời hạn</th></tr>
                        <tr><td>Cookie phiên làm việc</td><td>Ghi nhớ ảnh bạn vừa chọn, đơn thanh toán đang chờ và các kết quả bạn đã mở trong lần truy cập.</td><td>Khoảng 2 giờ kể từ lần hoạt động cuối</td></tr>
                        <tr><td>XSRF-TOKEN</td><td>Bảo vệ biểu mẫu khỏi các yêu cầu giả mạo từ website khác.</td><td>Như cookie phiên</td></tr>
                    </table>
                    <p>Chúng tôi hiện không dùng cookie quảng cáo hay cookie theo dõi của bên thứ ba. Nếu sau này có thay đổi, chính sách này sẽ được cập nhật và bạn sẽ được hỏi ý kiến trước khi các cookie đó được đặt.</p>
                    <h2>Quản lý cookie</h2>
                    <p>Bạn có thể xóa hoặc chặn cookie trong phần cài đặt của trình duyệt. Khi chặn cookie cần thiết, một số chức năng như tải ảnh, thanh toán và xem kết quả có thể không hoạt động.</p>
                    HTML,
            ],

            'huong-dan-su-dung' => [
                'title' => 'Hướng dẫn sử dụng',
                'summary' => 'Các bước xem tướng khuôn mặt, chuẩn bị ảnh và mở khóa phần luận giải chuyên sâu.',
                'meta_description' => 'Hướng dẫn xem tướng khuôn mặt tại Tâm Minh: chuẩn bị ảnh chân dung, nhập ngày giờ sinh và thanh toán mở khóa luận giải.',
                'content' => <<<'HTML'
                    <h2>1. Chuẩn bị ảnh chân dung</h2>
                    <ul>
                        <li>Chụp chính diện, mặt nhìn thẳng vào máy ảnh, đủ ánh sáng.</li>
                        <li>Để lộ trán và hai tai nếu có thể; không đeo kính, khẩu trang hay mũ.</li>
                        <li>Không dùng bộ lọc làm mịn da hoặc chỉnh sửa khuôn mặt.</li>
                        <li>Định dạng JPG, PNG hoặc WEBP.</li>
                    </ul>
                    <h2>2. Gửi thông tin</h2>
                    <ol>
                        <li>Tại trang chủ, chọn ảnh chân dung. Hệ thống sẽ nhận diện khuôn mặt ngay sau khi bạn chọn ảnh.</li>
                        <li>Nhập họ tên, giới tính và ngày sinh dương lịch. Giờ sinh không bắt buộc, nhưng có giờ sinh thì lá số đầy đủ hơn (có trụ giờ, cục mệnh và thân cư).</li>
                        <li>Đồng ý với điều khoản và bấm xem kết quả.</li>
                    </ol>
                    <h2>3. Đọc kết quả</h2>
                    <p>Trang kết quả hiện ngay bát tự và bản mệnh, sau đó các phần luận giải được viết dần trong khoảng một phút. Hãy lưu lại đường dẫn của trang kết quả để xem lại sau, vì đây là cách duy nhất để mở lại kết quả.</p>
                    <h2>4. Mở khóa phần chuyên sâu</h2>
                    <ol>
                        <li>Bấm nút mở khóa ở phần bạn muốn xem, hoặc chọn mở khóa trọn bộ để được giảm giá.</li>
                        <li>Quét mã QR bằng ứng dụng ngân hàng. Số tiền và nội dung chuyển khoản đã được điền sẵn; vui lòng không sửa nội dung.</li>
                        <li>Giữ nguyên trang. Khi nhận được tiền, trang tự tải lại và nội dung được viết trong vài phút.</li>
                    </ol>
                    <p>Ảnh chân dung được tự động xóa sau {$retentionDays} ngày. Các phần luận giải dựa trên ảnh (ngũ hành tướng pháp, thần khí sắc, tam đình, ngũ quan, thập nhị cung) chỉ mở khóa được trong thời gian này; nội dung đã mở khóa vẫn xem lại được sau đó.</p>
                    <h2>5. Lưu và in kết quả</h2>
                    <p>Dùng nút "Tải PDF" hoặc "In kết quả" ở đầu trang. Trong hộp thoại in, chọn "Lưu thành PDF" để lưu về máy.</p>
                    <p>Cần hỗ trợ thêm? Xem trang <a href="/lien-he">Liên hệ</a>.</p>
                    HTML,
            ],

            'lien-he' => [
                'title' => 'Liên hệ',
                'summary' => 'Đội ngũ Tâm Minh sẵn sàng hỗ trợ bạn về kết quả luận giải, thanh toán và dữ liệu cá nhân.',
                'meta_description' => 'Liên hệ Tâm Minh qua hotline, email hoặc địa chỉ văn phòng để được hỗ trợ về xem tướng, thanh toán và dữ liệu cá nhân.',
                'content' => <<<HTML
                    <p>Bạn có thể liên hệ Tâm Minh qua hotline hoặc email trong giờ làm việc ({$hours}). Chúng tôi phản hồi email trong vòng 24 giờ làm việc.</p>
                    <h2>Khi liên hệ, vui lòng gửi kèm</h2>
                    <ul>
                        <li><strong>Về kết quả luận giải:</strong> đường dẫn trang kết quả của bạn.</li>
                        <li><strong>Về thanh toán:</strong> mã đơn hàng (nội dung chuyển khoản) và ảnh chụp giao dịch.</li>
                        <li><strong>Yêu cầu xóa dữ liệu:</strong> đường dẫn trang kết quả cần xóa, xem thêm <a href="/chinh-sach-bao-mat">Chính sách bảo mật</a>.</li>
                    </ul>
                    <h2>Hợp tác</h2>
                    <p>Với đề nghị hợp tác nội dung, cộng tác viên hoặc chương trình giới thiệu, vui lòng gửi email tới {$email} với tiêu đề "Hợp tác".</p>
                    HTML,
            ],
        ];
    }
}
