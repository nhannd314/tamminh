<?php

namespace App\Support;

use App\AnalysisSection;
use App\Gender;
use Illuminate\Support\Str;

/**
 * Nội dung mẫu (lấy nguyên từ bản thiết kế) cho các phần luận giải chưa được Gemini sinh ra.
 * Cùng cấu trúc với JSON mà Gemini sẽ trả về và lưu ở FaceAnalysisSection::$content:
 *
 * - tags: list<string>, summary: string|list<string>, rating?: int (1-5)
 * - blocks: list<Block>, Block = array{title: string, description?: string, items: list<Item>}
 *   Item = array{label?: string, text?: string, paragraphs?: list<string>}
 * - tabs: list<array{title: string, subtitle: string, blocks: list<Block>, conclusion?: array{title: string, text: string}}>
 * - và các khoá riêng của từng phần (radar, strengths, ratios, palaces…).
 *
 * Trong văn bản, {anh} / {Anh} được thay bằng cách xưng hô theo giới tính.
 */
final class SampleAnalysis
{
    /**
     * @return array<string, mixed>
     */
    public static function for(AnalysisSection $section, Gender $gender = Gender::Male): array
    {
        $content = match ($section) {
            AnalysisSection::Overview => self::overview(),
            AnalysisSection::NguHanh => self::nguHanh(),
            AnalysisSection::ThanKhiSac => self::thanKhiSac(),
            AnalysisSection::TamDinh => self::tamDinh(),
            AnalysisSection::NguQuan => self::nguQuan(),
            AnalysisSection::ThapNhiCung => self::thapNhiCung(),
            AnalysisSection::PhongThuy => self::phongThuy(),
            AnalysisSection::Advice => self::advice(),
        };

        $pronoun = $gender === Gender::Female ? 'chị' : 'anh';

        array_walk_recursive($content, function (mixed &$value) use ($pronoun): void {
            if (is_string($value)) {
                $value = strtr($value, ['{anh}' => $pronoun, '{Anh}' => Str::ucfirst($pronoun)]);
            }
        });

        return $content;
    }

    /**
     * @return list<array{name: string, description: string, price: int, glyph: string}>
     */
    public static function products(): array
    {
        return [
            ['name' => 'Tỳ hưu bạc', 'description' => 'Chiêu tài, giữ lộc - hợp mệnh Kim', 'price' => 1290000, 'glyph' => '貔'],
            ['name' => 'Vòng thạch anh trắng', 'description' => 'Tăng cường năng lượng Kim, minh mẫn', 'price' => 690000, 'glyph' => '晶'],
            ['name' => 'Vòng mắt hổ vàng', 'description' => 'Thổ sinh Kim, vững vàng tài vận', 'price' => 790000, 'glyph' => '虎'],
            ['name' => 'Hồ lô đồng', 'description' => 'Hóa giải sát khí, bảo vệ sức khỏe', 'price' => 450000, 'glyph' => '葫'],
        ];
    }

    /**
     * @return array{label?: string, text?: string, paragraphs?: list<string>}
     */
    private static function item(?string $label, string ...$paragraphs): array
    {
        $item = $label === null ? [] : ['label' => $label];

        return count($paragraphs) === 1 ? [...$item, 'text' => $paragraphs[0]] : [...$item, 'paragraphs' => $paragraphs];
    }

    /**
     * @return array<string, mixed>
     */
    private static function overview(): array
    {
        return [
            'face_box' => [22, 8, 56, 56],
            'score' => 78,
            'verdict' => 'Vận khí khá tốt',
            'ratios' => ['good' => 65, 'neutral' => 25, 'caution' => 10],
            'destiny' => [
                'Nạp âm bản mệnh cho thấy khí chất cương trực, rõ ràng, có hoài bão lớn và ý chí bền bỉ trước khó khăn.',
                'Trong cấu trúc bát tự, các hành tương sinh giúp bồi đắp và tạo thế vững chắc cho bản mệnh. Tính cách chủ đạo là tư duy mạch lạc, ghét sự mập mờ và thích chinh phục những thử thách khó.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function nguHanh(): array
    {
        return [
            'tags' => ['Kim vượng', 'Thủy tương sinh'],
            'radar' => ['hoa' => 5, 'tho' => 15, 'kim' => 50, 'thuy' => 20, 'moc' => 10],
            'summary' => 'Khuôn mặt cân đối, đường nét cứng cáp, sắc da sáng, ánh mắt có thần. Ngũ hành hài hòa, Kim vượng sinh Thủy - chủ về trí tuệ, tài năng và ý chí mạnh mẽ.',
            'strengths' => ['Ý chí kiên cường', 'Quyết đoán', 'Có năng lực tổ chức', 'Hợp kinh doanh, quản lý'],
            'cautions' => ['Dễ nóng nảy khi áp lực', 'Cần mềm mỏng hơn trong giao tiếp'],
            'blocks' => [
                ['title' => 'Hình dáng khuôn mặt: mặt hình Mộc pha Kim', 'items' => [
                    self::item(null, 'Quan sát bức chân dung chính diện của {anh}, ta thấy rõ sự kết hợp hài hòa giữa khí chất của một người trí thức, nhà quản lý và nét sắc sảo của người hành nghề kỹ thuật, kinh doanh:'),
                    self::item('Khung xương và Trán', 'Trán cao, rộng, các góc trán đầy đặn, sáng sủa. Điều này cho thấy Thiên đình (cung Quan lộc, Phúc đức) rất vượng. Trí nhớ tốt, tư duy logic nhạy bén, khả năng tiếp thu công nghệ và quản trị hệ thống cực kỳ xuất sắc (rất phù hợp với lĩnh vực phát triển phần mềm, tư duy hệ thống và điều hành kinh doanh).'),
                    self::item('Xương gò má và Cằm (Địa khố)', 'Xương hàm và cằm đầy đặn, góc cạnh vừa phải tạo sự vững chãi ở phần hạ đình. Điều này thể hiện ý chí kiên định, khả năng bứt phá khi gặp khó khăn, càng về trung vận và hậu vận nền tảng sự nghiệp càng vững chắc, có hậu.'),
                ]],
            ],
            'balance' => [
                ['element' => 'hoa', 'title' => 'Tăng Hỏa', 'hint' => 'Màu đỏ, tím, cam'],
                ['element' => 'kim', 'title' => 'Giảm Kim', 'hint' => 'Tránh sắc trắng'],
                ['element' => 'moc', 'title' => 'Tăng Mộc', 'hint' => 'Màu xanh lá cây'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function thanKhiSac(): array
    {
        return [
            'tags' => ['Thần thái tốt', 'Vận khí sáng'],
            'summary' => 'Thần thái sáng sủa, ánh mắt có thần, khí sắc hồng hào. Tướng mạo đoan chính, khí chất tự tin, dễ tạo thiện cảm với người đối diện.',
            'rating' => 4,
            'blocks' => [
                ['title' => '1. Về Thần (Tinh thần, Ý chí và Thần thái)', 'items' => [
                    self::item('Biểu hiện', 'Thần thái của {anh} toát lên sự điềm tĩnh, tập trung và sắc sảo. Ánh mắt sáng, lòng đen lòng trắng phân minh, nhìn thẳng về phía trước với sự tự tin nhưng không hề phô trương.'),
                    self::item('Luận giải', 'Trong nhân tướng, "Thần thu lại thì trí tuệ mẫn tiệp". Đôi mắt và thần thái này cho thấy {anh} là người có nội lực vững vàng, tư duy lý trí, khả năng phán đoán tình huống nhanh nhạy và kiểm soát cảm xúc rất tốt. Đây là tướng của người làm chủ bản thân, dù đối diện với áp lực công việc hay biến động lớn cũng giữ được sự tỉnh táo để đưa ra quyết định chính xác.'),
                ]],
                ['title' => '2. Về Khí (Sinh khí, Năng lượng và Hơi thở sống)', 'items' => [
                    self::item('Biểu hiện', 'Khí sắc tổng thể gọn gàng, tư thế khoanh tay tự tin, dáng ngồi vững chãi, cơ mặt cân đối và vùng da quanh miệng, mũi có độ sáng khỏe mạnh.'),
                    self::item('Luận giải', 'Khí biểu thị cho năng lượng vận hành bên trong cơ thể (sinh lực). Khí của {anh} vượng và quy tụ, không bị tán loạn. Điều này thể hiện nguồn năng lượng hành động cao, sự bền bỉ trong công việc (rất phù hợp với nhịp độ làm việc của người làm công nghệ, quản trị hệ thống hay kinh doanh đòi hỏi sự tập trung dài hạn). Khí huyết lưu thông tốt cũng là nền tảng mang lại sự uy tín và tạo được sự tin cậy lớn đối với người đối diện.'),
                ]],
                ['title' => '3. Về Sắc (Sắc diện, Màu sắc da và Vận hạn ngắn hạn)', 'items' => [
                    self::item('Biểu hiện', 'Da mặt sáng sủa, sắc da tươi tắn, không bị xám xịt hay có trọc điểm (vết ố tối màu) bất thường ở các cung quan trọng như Thiên đình (trán) hay Chuẩn đầu (mũi). Vùng quanh ấn đường (giữa hai lông mày) thanh thoát.'),
                    self::item('Luận giải', 'Sắc phản ánh vận trình ở hiện tại và tương lai gần. Sắc diện sáng và sạch sẽ cho thấy trong giai đoạn này, tinh thần {anh} đang ở trạng thái cân bằng tốt, các cơ hội trong công việc và tài lộc đang có đà thuận lợi, hanh thông. Sự hài hòa giữa Thần - Khí - Sắc hiện tại cho thấy {anh} đang duy trì được phong độ đỉnh cao, nội tại vững chắc để sẵn sàng chinh phục các mục tiêu lớn tiếp theo.'),
                ]],
                ['title' => 'Tổng kết', 'items' => [
                    self::item(null, 'Bộ vị Thần tàng, Khí tụ, Sắc sáng kết hợp với nhau tạo nên một tổng thể tướng mạo của người có tài lãnh đạo, trí tuệ sắc bén và phúc khí tốt. {Anh} hãy tiếp tục phát huy sự tập trung và năng lượng tích cực này trong công việc và cuộc sống nhé!'),
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function tamDinh(): array
    {
        return [
            'tags' => ['Trung đình tốt', 'Hậu vận an'],
            'ratios' => [31, 36, 33],
            'summary' => [
                'Tam đình cân đối, trung đình phát triển tốt, hậu đình đầy đặn - chủ về trung vận phát triển mạnh, hậu vận an nhàn phú quý.',
                'Xét tương quan, ba phần gần đều nhau (31% · 36% · 33%) - thế "tam đình bình đẳng", chủ về vận khí liền mạch, ít thăng trầm. Trung đình nhỉnh hơn cho thấy 31-50 tuổi là đỉnh cao sự nghiệp; Hạ đình cân xứng giúp giữ vững thành quả cho hậu vận.',
            ],
            'rating' => 4,
            'tabs' => [
                [
                    'title' => 'Thượng đình',
                    'subtitle' => '15–30 tuổi',
                    'blocks' => [
                        ['title' => '1. Vầng trán (Thiên đình)', 'items' => [
                            self::item('Đặc điểm', 'Trán cao, rộng, các góc trán đầy đặn và có độ phẳng, sáng sủa, không bị lõm hay có vết hằn thô. Đường chân tóc tương đối gọn gàng.'),
                            self::item('Luận giải',
                                'Trán trong nhân tướng đại diện cho Thiên đình (trời cho), phản ánh trí tuệ bẩm sinh, khả năng tư duy logic, tiếp thu kiến thức và nền tảng gia giáo từ thủa nhỏ.',
                                'Trán cao và rộng cho thấy {anh} sở hữu một bộ óc nhạy bén, khả năng phân tích hệ thống cực kỳ xuất sắc (rất phù hợp với các công việc đòi hỏi tư duy công nghệ, lập trình, quản trị chiến lược hoặc điều hành hệ thống phức tạp).',
                            ),
                        ]],
                        ['title' => '2. Các Cung Chủ Đạo ở Thượng đình', 'items' => [
                            self::item('Cung Quan Lộc (Chính giữa trán)', 'Vùng này đầy đặn và sáng sủa, thể hiện con đường sự nghiệp có tầm nhìn, khả năng thăng tiến tự thân hoặc làm chủ công việc lớn rất tốt, có uy tín trong lĩnh vực chuyên môn.'),
                            self::item('Cung Phúc Đức (Hai góc bên trên trán - góc trán)', 'Đầy đặn, không bị khuyết hãm. Điều này cho thấy phước đức tổ tiên tốt, đồng thời bản thân là người có tầm nhìn xa trông rộng, biết lo xa và có sự nhạy bén trong kinh doanh, đầu tư.'),
                            self::item('Cung Thiên Mệnh / Phụ Mẫu', 'Phần trán phát triển cân đối cũng biểu thị sự thuận lợi trong việc nhận được sự hỗ trợ từ quý nhân, các mối quan hệ xã hội hoặc gia đình ở giai đoạn đầu đời.'),
                        ]],
                        ['title' => '3. Ấn Đường (Giao điểm giữa hai lông mày - giáp ranh Thượng và Trung đình)', 'items' => [
                            self::item('Đặc điểm', 'Khoảng cách giữa hai lông mày (Ấn đường) rộng rãi, thoáng đãng, da nhẵn nhụi, không bị lông mày mọc lấn át hay có nếp nhăn sâu cản trở.'),
                            self::item('Luận giải', 'Ấn đường được ví như "quang minh điện", là nơi thể hiện tầm độ lượng, ý chí và vận khí ngắn hạn. Ấn đường rộng mở cho thấy {anh} là người phóng khoáng, tư duy cởi mở, không chấp nhặt tiểu tiết, dễ tạo được sự tin tưởng lớn từ đối tác, khách hàng hoặc những người xung quanh.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Thượng đình', 'text' => 'Thượng đình sáng sủa, cao rộng và đầy đặn là thế tướng của trí tuệ và sự mẫn tiệp. Nó bổ trợ hoàn hảo cho bản mệnh Kiếm Phong Kim, giúp {anh} luôn giữ được sự sắc sảo trong quyết định, tầm nhìn chiến lược dài hạn trong kinh doanh cũng như khả năng làm chủ các dự án lớn một cách vững chắc.'],
                ],
                [
                    'title' => 'Trung đình',
                    'subtitle' => '31–50 tuổi',
                    'blocks' => [
                        ['title' => 'Từ Ấn đường đến Chóp mũi — Tướng Hành Động, Ý Chí & Tài Lộc', 'items' => [
                            self::item('Đặc điểm hình tướng', 'Sống mũi thẳng, cao vừa phải, chuẩn đầu (chóp mũi) đầy đặn, cánh mũi thâu tóm kín đáo; kết hợp với đôi mày thanh gọn, sắc bén và ánh mắt tinh anh.'),
                            self::item('Ý chí và Hành động thực tế', 'Trung đình đại diện cho quãng đời trung vận (giai đoạn sung sức nhất của sự nghiệp từ 30 đến 50 tuổi) và khả năng thực thi. Sống mũi thẳng tượng trưng cho sự chính trực, tự lực cánh sinh, dám nghĩ dám làm và không chùn bước trước khó khăn.'),
                            self::item('Cung Tài Lộc (Tị lương và Giám đài - Hộ chiếu)', 'Chuẩn đầu và cánh mũi đầy đặn, thu gọn là dấu hiệu của tài vận vượng phát. Điều này cho thấy {anh} có duyên lớn với kinh doanh, buôn bán (quản lý cửa hàng, thương mại điện tử), biết cách tạo ra dòng tiền, quản lý tài chính nhạy bén và tích lũy bền vững.'),
                            self::item('Khả năng quản trị con người', 'Ánh mắt sáng kết hợp với bộ lông mày sắc nét thể hiện tính kỷ luật cao, quyết đoán trong công việc, biết nắm bắt thời cơ và quản lý đội ngũ hoặc hệ thống kinh doanh rất hiệu quả.'),
                        ]],
                    ],
                ],
                [
                    'title' => 'Hạ đình',
                    'subtitle' => '51+ tuổi',
                    'blocks' => [
                        ['title' => '1. Cấu trúc Cằm và Địa Khố (Phần đáy của Hạ đình)', 'items' => [
                            self::item('Đặc điểm', 'Xương hàm và cằm đầy đặn, có độ rộng và vuông vức vừa phải, không bị nhọn hoắt hay thụt lùi vào trong.'),
                            self::item('Luận giải',
                                'Cằm trong nhân tướng được ví như Địa khố (kho chứa của đất), tượng trưng cho nền tảng vững chắc cuối đời. Cằm đầy đặn và cân đối cho thấy hậu vận của {anh} rất vững vàng, an nhàn, không phải lo lắng về vấn đề vật chất.',
                                'Sự vững chãi ở vùng cằm còn thể hiện sự kiên định, quyết tâm đến cùng trong công việc. Những gì {anh} gieo trồng, xây dựng từ thời trẻ (Thượng đình và Trung đình) sẽ đơm hoa kết trái và được bảo toàn, tích lũy trọn vẹn ở giai đoạn này.',
                            ),
                        ]],
                        ['title' => '2. Xương hàm và Góc hàm (Cung Nô bộc)', 'items' => [
                            self::item('Đặc điểm', 'Xương góc hàm phát triển rõ nét, tạo khung mặt săn chắc và có lực.'),
                            self::item('Luận giải',
                                'Phần xương hàm thể hiện sức bật, ý chí sinh tồn và khả năng quản lý con người (đội ngũ, nhân viên, cộng sự hoặc người làm việc cùng).',
                                'Góc hàm vững vàng cho thấy {anh} có uy tín, phong thái quản lý kỷ luật nhưng công tâm, rất dễ thu phục lòng người, xây dựng được những mối quan hệ hợp tác hoặc đội ngũ gắn bó lâu dài trong kinh doanh và công việc.',
                            ),
                        ]],
                        ['title' => '3. Nhân trung và Vùng quanh miệng (Nối liền Trung và Hạ đình)', 'items' => [
                            self::item('Đặc điểm', 'Đường nhân trung sâu, rõ nét, trên hẹp dưới rộng (hình phễu); kết hợp với bờ môi đầy đặn.'),
                            self::item('Luận giải',
                                'Nhân trung là "thọ đường" và cũng là nơi phản ánh phúc lộc về con cái (Cung Tử tức). Nhân trung sâu và rộng về phía dưới cho thấy đường con cái thuận lợi, con cái ngoan ngoãn, hiếu thảo và sau này sẽ là chốn nương tựa, niềm tự hào của cha mẹ.',
                                'Phúc khí ở vùng này cũng bổ trợ cho sức khỏe thể chất, sự dẻo dai và tuổi thọ về già luôn được duy trì ở mức tốt.',
                            ),
                        ]],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function nguQuan(): array
    {
        return [
            'tags' => ['Quan sắc hài hòa', 'Tài lộc tốt'],
            'summary' => [
                'Ngũ quan cân đối, các bộ phận hài hòa, chủ về lòng nhân ái, trí tuệ và tài lộc.',
                'Trong năm quan, Mắt và Mũi là hai điểm nổi bật nhất: thần mắt sáng giúp {anh} nhìn người, nắm thời cơ chuẩn xác, còn mũi thẳng đầy đặn giữ vững tài khố. Lông mày thanh tú và miệng hài hòa bổ trợ cho khả năng giao tiếp, tạo dựng quan hệ; đôi tai dày dài mang phúc khí bền lâu. Tổng thể là tướng "ngũ quan đều thành", chủ về một đời ít trắc trở, sự nghiệp và tài lộc tiến triển vững vàng.',
            ],
            'tabs' => [
                [
                    'title' => 'Lông mày',
                    'subtitle' => 'Bảo thọ quan',
                    'blocks' => [
                        ['title' => '1. Hình dáng và Độ dài mày', 'items' => [
                            self::item('Đặc điểm', 'Lông mày thanh gọn, sợi mày mọc xuôi đều, đuôi mày dài vượt khóe mắt, sắc nét nhưng không rậm rạp hay giao nhau ở Ấn đường.'),
                            self::item('Luận giải',
                                'Lông mày là Bảo thọ quan, phản ánh tính cách, tình cảm anh em bạn bè và tuổi thọ. Mày thanh tú, dài quá mắt cho thấy {anh} là người ôn hòa, biết cư xử, được bạn bè và đồng nghiệp quý mến.',
                                'Đuôi mày gọn, không tán loạn biểu thị khả năng giữ được các mối quan hệ lâu bền, thường gặp quý nhân phù trợ đúng lúc trong công việc.',
                            ),
                        ]],
                        ['title' => '2. Cung Huynh Đệ', 'items' => [
                            self::item('Luận giải', 'Khoảng mày sáng, không có vết đứt hay nốt ruồi xấu - chủ về quan hệ anh chị em hòa thuận, có thể hỗ trợ lẫn nhau trong kinh doanh.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Lông mày', 'text' => 'Mày thanh tú bổ trợ cho trí tuệ của Thượng đình, giúp {anh} vừa sắc sảo trong tư duy vừa mềm mỏng trong giao tiếp - nền tảng tốt để xây dựng mạng lưới quan hệ rộng.'],
                ],
                [
                    'title' => 'Mắt',
                    'subtitle' => 'Giám sát quan',
                    'blocks' => [
                        ['title' => '1. Thần mắt', 'items' => [
                            self::item('Đặc điểm', 'Mắt dài vừa phải, lòng đen lòng trắng phân minh, ánh nhìn tập trung, sáng và có chiều sâu; mí mắt gọn gàng.'),
                            self::item('Luận giải',
                                'Mắt là Giám sát quan, nơi tàng chứa thần khí của con người. Ánh mắt có thần cho thấy {anh} có trí tuệ nhạy bén, quan sát tinh tường, phán đoán nhanh và ít bị người khác qua mặt.',
                                'Đây cũng là tướng của người có khả năng lãnh đạo, biết nhìn người và dùng người đúng chỗ.',
                            ),
                        ]],
                        ['title' => '2. Cung Điền Trạch và Cung Phu Thê', 'items' => [
                            self::item('Cung Điền Trạch (mí trên)', 'Rộng và đầy đặn, chủ về có duyên với nhà đất, tài sản cố định tích lũy dần theo thời gian.'),
                            self::item('Cung Phu Thê (đuôi mắt)', 'Đuôi mắt bằng phẳng, không có vết nhăn xấu - hôn nhân hòa hợp, vợ chồng đồng lòng.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Mắt', 'text' => 'Thần mắt tốt là điểm sáng nhất trong ngũ quan, thể hiện nội lực và sự tỉnh táo - giúp {anh} nắm bắt cơ hội và tránh rủi ro trong các quyết định lớn.'],
                ],
                [
                    'title' => 'Tai',
                    'subtitle' => 'Thái thính quan',
                    'blocks' => [
                        ['title' => '1. Hình dáng và Vị trí tai', 'items' => [
                            self::item('Đặc điểm', 'Vành tai dày, dài, ôm sát đầu; dái tai đầy đặn, màu sắc sáng hơn da mặt; đỉnh tai ngang hoặc cao hơn đuôi mày.'),
                            self::item('Luận giải',
                                'Tai là Thái thính quan, phản ánh phúc khí bẩm sinh, sức khỏe và vận trình thời thơ ấu. Tai dày dài cho thấy {anh} có nền tảng sức khỏe tốt, phúc đức gia đình dày.',
                                'Tai cao hơn mày biểu thị trí thông minh sớm phát triển, khả năng học hỏi và lắng nghe tốt.',
                            ),
                        ]],
                        ['title' => '2. Dái tai (Thùy châu)', 'items' => [
                            self::item('Luận giải', 'Dái tai đầy đặn là dấu hiệu của phúc lộc và tuổi thọ, về già sống an nhàn, được con cháu kính trọng.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Tai', 'text' => 'Đôi tai có phúc khí giúp vận số ổn định từ sớm, là chỗ dựa vững chắc cho sự nghiệp ở trung vận và cuộc sống an hưởng ở hậu vận.'],
                ],
                [
                    'title' => 'Mũi',
                    'subtitle' => 'Thẩm biện quan',
                    'blocks' => [
                        ['title' => '1. Sống mũi và Chuẩn đầu', 'items' => [
                            self::item('Đặc điểm', 'Sống mũi thẳng, cao vừa phải, không gồ xương; chuẩn đầu tròn đầy, cánh mũi cân đối và lỗ mũi không lộ.'),
                            self::item('Luận giải',
                                'Mũi là Thẩm biện quan, đồng thời là Cung Tài Bạch - trung tâm của tài lộc. Sống mũi thẳng cho thấy {anh} chính trực, có lập trường vững vàng.',
                                'Chuẩn đầu đầy và lỗ mũi kín biểu thị khả năng kiếm tiền tốt và biết giữ của, tài sản tích lũy bền vững.',
                            ),
                        ]],
                        ['title' => '2. Sơn căn (Gốc mũi)', 'items' => [
                            self::item('Luận giải', 'Sơn căn liền mạch với Ấn đường, không bị gãy hay lõm - chủ về sức khỏe ổn định và vận trình ở tuổi 41-43 thuận lợi.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Mũi', 'text' => 'Mũi là trụ cột của Trung đình; tướng mũi tốt kết hợp mệnh Kiếm Phong Kim giúp tài vận vượng, đặc biệt phù hợp với kinh doanh và đầu tư.'],
                ],
                [
                    'title' => 'Miệng',
                    'subtitle' => 'Xuất nạp quan',
                    'blocks' => [
                        ['title' => '1. Hình dáng môi và miệng', 'items' => [
                            self::item('Đặc điểm', 'Miệng rộng vừa phải, khóe miệng hơi hướng lên; môi đầy đặn, đường viền môi rõ nét, môi trên và môi dưới cân xứng.'),
                            self::item('Luận giải',
                                'Miệng là Xuất nạp quan, phản ánh khả năng giao tiếp, lộc ăn uống và mối quan hệ với cấp dưới. Miệng rộng vừa cho thấy {anh} nói năng có trọng lượng, lời nói tạo được sự tin tưởng.',
                                'Khóe miệng hướng lên biểu thị tinh thần lạc quan, dễ gây thiện cảm và được người khác giúp đỡ.',
                            ),
                        ]],
                        ['title' => '2. Lộc ăn và Lộc nói', 'items' => [
                            self::item('Luận giải', 'Môi đầy, sắc môi hồng hào - chủ về có lộc ăn uống, đời sống vật chất sung túc; lời nói có sức thuyết phục, thuận lợi cho đàm phán và kinh doanh.'),
                        ]],
                    ],
                    'conclusion' => ['title' => 'Tổng kết về Miệng', 'text' => 'Miệng hài hòa giúp Hạ đình thêm vững, hỗ trợ khả năng quản lý con người và mang lại cuộc sống đủ đầy về sau.'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function thapNhiCung(): array
    {
        $palaces = [
            ['Mệnh cung', 'Cát', 'Ấn đường', 'Bản thân có chí hướng rõ ràng, vận khí hanh thông.',
                'Ấn đường rộng, sáng, không vết lõm - bản thân có chí hướng rõ ràng, tâm tính phóng khoáng, vận khí tổng thể hanh thông.',
                'Giữ tinh thần lạc quan, tránh cau mày lâu ngày để Ấn đường luôn thông thoáng.'],
            ['Tài bạch cung', 'Cát', 'Mũi', 'Tài vận vượng, biết kiếm tiền và giữ của.',
                'Chuẩn đầu đầy, cánh mũi thu gọn - tài vận vượng, biết kiếm tiền và giữ của, có khả năng tích lũy tài sản lớn.',
                'Giai đoạn 41-50 tuổi là thời điểm tốt để mở rộng đầu tư, ưu tiên tài sản bền vững.'],
            ['Huynh đệ cung', 'Cát', 'Lông mày', 'Anh em hòa thuận, bạn bè giúp đỡ.',
                'Mày thanh, dài quá mắt - anh em hòa thuận, bạn bè giúp đỡ, có nhiều mối quan hệ hỗ trợ trong công việc.',
                'Có thể hợp tác kinh doanh cùng người thân hoặc bạn lâu năm, nên rõ ràng về tài chính.'],
            ['Điền trạch cung', 'Cát', 'Mí mắt trên', 'Có duyên với nhà đất, tài sản tăng dần.',
                'Mí trên rộng, đầy đặn - có duyên với nhà đất, tài sản cố định tăng dần theo thời gian.',
                'Nên cân nhắc đầu tư bất động sản theo hướng tích lũy dài hạn.'],
            ['Tử tức cung', 'Cát', 'Dưới mắt (lệ đường)', 'Con cái ngoan ngoãn, hiếu thảo.',
                'Vùng dưới mắt đầy, sắc sáng - con cái ngoan ngoãn, hiếu thảo, là niềm tự hào của gia đình.',
                'Dành thời gian đồng hành cùng con trong giai đoạn học tập để phát huy phúc khí.'],
            ['Nô bộc cung', 'Cát', 'Hai bên cằm', 'Được cấp dưới tin phục, đội ngũ gắn bó.',
                'Góc hàm vững, cằm đầy - được cấp dưới tin phục, dễ xây dựng đội ngũ gắn bó lâu dài.',
                'Phù hợp vai trò quản lý, nên trao quyền và ghi nhận đóng góp của cộng sự.'],
            ['Phu thê cung', 'Trung bình', 'Đuôi mắt (gian môn)', 'Hôn nhân ổn định, cần vun đắp thêm.',
                'Đuôi mắt bằng phẳng nhưng hơi tối - hôn nhân ổn định, cần dành thêm thời gian vun đắp tình cảm.',
                'Cân bằng giữa công việc và gia đình; những chuyến đi chung giúp gắn kết vợ chồng.'],
            ['Tật ách cung', 'Trung bình', 'Sơn căn', 'Sức khỏe tốt, chú ý dạ dày và giấc ngủ.',
                'Sơn căn liền mạch, hơi thấp - sức khỏe nền tảng tốt, nên chú ý dạ dày và giấc ngủ khi áp lực công việc cao.',
                'Duy trì giờ giấc điều độ, khám sức khỏe định kỳ mỗi năm.'],
            ['Thiên di cung', 'Cát', 'Hai góc trán', 'Thuận lợi khi đi xa, mở rộng kinh doanh.',
                'Góc trán đầy, sáng - thuận lợi khi đi xa, công tác, mở rộng kinh doanh ra ngoài địa phương.',
                'Hướng Tây và Tây Bắc (hành Kim) là hướng phát triển thuận lợi.'],
            ['Quan lộc cung', 'Cát', 'Giữa trán', 'Sự nghiệp thăng tiến, có uy tín.',
                'Trán cao rộng, không vết hằn - sự nghiệp thăng tiến, có uy tín trong lĩnh vực chuyên môn.',
                'Tập trung xây dựng chuyên môn sâu và thương hiệu cá nhân trong ngành.'],
            ['Phúc đức cung', 'Trung bình', 'Trên đuôi mày', 'Phúc khí ổn, nên làm việc thiện.',
                'Vùng phúc đức khá đầy nhưng chưa nổi bật - phúc khí ổn, nên làm việc thiện để tăng thêm phúc phần.',
                'Tham gia hoạt động thiện nguyện, giúp đỡ người khó khăn để tích phúc.'],
            ['Phụ mẫu cung', 'Cần lưu ý', 'Nhật giác - Nguyệt giác', 'Nên quan tâm sức khỏe cha mẹ.',
                'Hai góc trán trên không đều - nên quan tâm sức khỏe cha mẹ, dành thời gian chăm sóc gia đình nhiều hơn.',
                'Thường xuyên thăm hỏi, đưa cha mẹ đi khám sức khỏe định kỳ.'],
        ];

        return [
            'tags' => ['Cung mệnh tốt', 'Nhiều cát tinh'],
            'summary' => '12 cung trên khuôn mặt cho thấy vận mệnh tổng thể tốt, nhiều cung cát tinh, ít hung tinh. Các cung trọng yếu về bản thân, tài lộc và sự nghiệp đều sáng sủa; chỉ Phụ mẫu cung cần lưu tâm thêm.',
            'palaces' => array_map(fn (array $palace): array => [
                'name' => $palace[0],
                'verdict' => $palace[1],
                'position' => $palace[2],
                'text' => $palace[3],
                'analysis' => $palace[4],
                'advice' => $palace[5],
            ], $palaces),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function phongThuy(): array
    {
        return [
            'tags' => ['Hướng tốt', 'Tài lộc vượng'],
            'summary' => 'Bản mệnh Kiếm Phong Kim, Kim vượng (50%) - nên dùng Thổ để sinh Kim, Kim để trợ mệnh; hạn chế yếu tố Hỏa. Ứng dụng đúng giúp tăng cường vận khí, hút tài lộc, sức khỏe và may mắn.',
            'blocks' => [
                ['title' => 'Màu sắc phù hợp', 'description' => 'Màu tương sinh, tương hợp với bản mệnh để hút tài lộc, may mắn', 'items' => [
                    self::item('Bản mệnh', 'Trắng, bạc, ánh kim'),
                    self::item('Tương sinh', 'Vàng, nâu đất (Thổ sinh Kim)'),
                    self::item('Nên tránh', 'Đỏ, hồng, tím (Hỏa khắc Kim)'),
                ]],
                ['title' => 'Hướng phong thủy', 'description' => 'Hướng nhà, hướng bàn làm việc, hướng ngủ tốt cho từng năm', 'items' => [
                    self::item('Hướng nhà', 'Tây, Tây Bắc (Kim) hoặc Tây Nam, Đông Bắc (Thổ)'),
                    self::item('Bàn làm việc', 'Ngồi quay mặt về Tây Bắc, lưng tựa tường vững'),
                    self::item('Hướng ngủ', 'Đầu giường hướng Tây hoặc Tây Nam'),
                    self::item('Năm 2027', 'Tài tinh ở Tây Bắc, nên đặt két sắt, bàn làm việc tại đây'),
                ]],
                ['title' => 'Đồ vật phong thủy', 'description' => 'Vật phẩm cát tường, đá quý, linh vật hộ mệnh nên đeo bên người', 'items' => [
                    self::item('Đá quý', 'Thạch anh trắng, mắt hổ vàng, citrine'),
                    self::item('Linh vật', 'Tỳ hưu bạc, Kỳ lân, Hồ lô đồng'),
                    self::item('Trang sức', 'Vòng tay hoặc nhẫn bạc, vàng trắng'),
                    self::item('Đặt tại nhà', 'Chuông gió kim loại ở hướng Tây'),
                ]],
                ['title' => 'Tuổi hợp làm ăn', 'description' => 'Tuổi tương sinh để hợp tác, kết giao, tránh tuổi xung khắc', 'items' => [
                    self::item('Tam hợp', 'Tý, Thìn - hợp tác lâu dài, cùng phát triển'),
                    self::item('Lục hợp', 'Tỵ - quý nhân hỗ trợ tài chính'),
                    self::item('Nên tránh', 'Dần (lục xung), Hợi (lục hại)'),
                    self::item('Hợp mệnh', 'Người mệnh Thổ, mệnh Thủy'),
                ]],
                ['title' => 'Số & ngày tốt', 'description' => 'Con số may mắn, ngày giờ hoàng đạo để khởi sự, ký kết, xuất hành', 'items' => [
                    self::item('Số may mắn', '6, 7 (Kim) và 2, 5, 8 (Thổ)'),
                    self::item('Ngày tốt', 'Ngày Tý, Thìn, Thân; tránh ngày Dần'),
                    self::item('Giờ hoàng đạo', 'Giờ Thìn (7–9h), giờ Thân (15–17h)'),
                    self::item('Khởi sự', 'Mùa thu và các tháng cuối năm âm lịch'),
                ]],
                ['title' => 'Hóa giải sát khí', 'description' => 'Cách bố trí, trấn yểm hóa giải hung tinh và điểm xấu trong nhà', 'items' => [
                    self::item('Bếp lửa', 'Tránh đặt bếp ở Tây Bắc; dùng đá vàng làm trung gian'),
                    self::item('Góc nhọn', 'Che chắn bằng cây xanh lá tròn hoặc rèm'),
                    self::item('Cửa đối cửa', 'Treo rèm hoặc đặt bình phong gỗ'),
                    self::item('Gương', 'Không đặt gương đối diện giường ngủ'),
                ]],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function advice(): array
    {
        return [
            'tags' => ['Tích đức hành thiện', 'Vận mệnh tốt hơn'],
            'summary' => 'Vận mệnh do trời định một phần, phần còn lại do người tạo. Kết hợp tu tâm, hành động đúng thời và hỗ trợ từ phong thủy sẽ giúp {anh} phát huy tối đa thế mạnh Kim vượng, hóa giải điểm yếu và đón vận tốt.',
            'blocks' => [
                ['title' => 'Tâm pháp', 'description' => 'Tu tâm dưỡng tính, thiền định, hành thiện - gốc rễ của mọi cải vận.', 'items' => [
                    self::item('Tu tâm', 'Bớt nóng vội, học cách lắng nghe - Kim vượng dễ cứng rắn, cần mềm mỏng'),
                    self::item('Thiền định', '10–15 phút mỗi sáng để giữ đầu óc minh mẫn, quyết định sáng suốt'),
                    self::item('Hành thiện', 'Giúp đỡ người khó khăn, đặc biệt quan tâm cha mẹ để bồi Phụ mẫu cung'),
                ]],
                ['title' => 'Hành động', 'description' => 'Việc nên làm, việc nên tránh trong từng đại vận và lưu niên.', 'items' => [
                    self::item('Đại vận 32–41', 'Tích lũy chuyên môn, xây dựng đội ngũ, mở rộng quan hệ'),
                    self::item('Đại vận 42–51', 'Thời điểm bứt phá - nên làm chủ, đầu tư dài hạn'),
                    self::item('Nên làm', 'Hợp tác người tuổi Tý, Thìn; giữ tài chính minh bạch'),
                    self::item('Nên tránh', 'Đầu tư mạo hiểm, bảo lãnh tài chính cho người khác'),
                ]],
                ['title' => 'Thời điểm', 'description' => 'Năm tốt, tháng tốt, ngày giờ hoàng đạo cho các quyết định lớn.', 'items' => [
                    self::item('Năm tốt', '2028 (Mậu Thân), 2032 (Nhâm Tý) - vận Thổ, Kim vượng'),
                    self::item('Tháng tốt', 'Tháng 7, 8 âm lịch (mùa thu, Kim vượng)'),
                    self::item('Ngày giờ', 'Ngày Tý, Thìn, Thân; giờ Thìn (7–9h), giờ Thân (15–17h)'),
                    self::item('Cần thận trọng', 'Năm 2034 (Giáp Dần) - xung Thái tuế'),
                ]],
                ['title' => 'Vật phẩm', 'description' => 'Linh vật, vật phẩm phong thủy, hướng nhà - hỗ trợ thực tế.', 'items' => [
                    self::item('Linh vật', 'Tỳ hưu bạc đặt hướng Tây Bắc phòng làm việc'),
                    self::item('Đeo bên người', 'Vòng thạch anh trắng hoặc mắt hổ vàng'),
                    self::item('Hướng nhà', 'Tây, Tây Bắc, Tây Nam, Đông Bắc'),
                    self::item('Bố trí', 'Phòng khách sáng, nhiều gam trắng, vàng, nâu đất'),
                ]],
            ],
        ];
    }
}
