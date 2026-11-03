<?php

namespace App\Support;

use App\AnalysisSection;
use App\Models\FaceAnalysis;
use App\Support\Astrology\BirthChart;
use App\Support\Astrology\Element;
use App\Support\Astrology\Pillar;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Sinh nội dung luận giải bằng Gemini, cùng cấu trúc với SampleAnalysis.
 * Hỗ trợ mọi phần: các phần đọc tướng mặt (Ngũ hành, Thần khí sắc, Tam đình, Ngũ quan, Thập nhị cung) gửi kèm ảnh khuôn mặt;
 * Tổng quan, Phong thủy và Lời khuyên chỉ dựa trên bát tự (kèm kết quả các phần trước nếu đã có).
 */
class AnalysisGenerator
{
    private const SUPPORTED = [
        AnalysisSection::Overview, AnalysisSection::NguHanh, AnalysisSection::ThanKhiSac, AnalysisSection::TamDinh,
        AnalysisSection::NguQuan, AnalysisSection::ThapNhiCung, AnalysisSection::PhongThuy, AnalysisSection::Advice,
    ];

    /**
     * Các nhóm thẻ cố định (block-cards) theo phần: khoá nhóm => [tiêu đề, mô tả, [khoá mục => nhãn]].
     * Nhãn `:year` thay bằng năm hiện tại.
     */
    private const CARDS = [
        'phong_thuy' => self::FENG_SHUI,
        'advice' => [
            'tam_phap' => ['Tâm pháp', 'Tu tâm dưỡng tính, thiền định, hành thiện - gốc rễ của mọi cải vận.', [
                'tu_tam' => 'Tu tâm', 'thien_dinh' => 'Thiền định', 'hanh_thien' => 'Hành thiện',
            ]],
            'hanh_dong' => ['Hành động', 'Việc nên làm, việc nên tránh trong từng giai đoạn.', [
                'hien_tai' => 'Hiện tại', 'muoi_nam_toi' => 'Mười năm tới', 'nen_lam' => 'Nên làm', 'nen_tranh' => 'Nên tránh',
            ]],
            'thoi_diem' => ['Thời điểm', 'Năm tốt, tháng tốt, ngày giờ hoàng đạo cho các quyết định lớn.', [
                'nam_tot' => 'Năm tốt', 'thang_tot' => 'Tháng tốt', 'ngay_gio' => 'Ngày giờ', 'than_trong' => 'Cần thận trọng',
            ]],
            'vat_pham' => ['Vật phẩm', 'Linh vật, vật phẩm phong thủy, hướng nhà - hỗ trợ thực tế.', [
                'linh_vat' => 'Linh vật', 'deo_ben_nguoi' => 'Đeo bên người', 'huong_nha' => 'Hướng nhà', 'bo_tri' => 'Bố trí',
            ]],
        ],
    ];

    /**
     * 6 nhóm chỉ dẫn phong thủy.
     */
    private const FENG_SHUI = [
        'mau_sac' => ['Màu sắc phù hợp', 'Màu tương sinh, tương hợp với bản mệnh để hút tài lộc, may mắn', [
            'ban_menh' => 'Bản mệnh', 'tuong_sinh' => 'Tương sinh', 'nen_tranh' => 'Nên tránh',
        ]],
        'huong' => ['Hướng phong thủy', 'Hướng nhà, hướng bàn làm việc, hướng ngủ tốt cho từng năm', [
            'huong_nha' => 'Hướng nhà', 'ban_lam_viec' => 'Bàn làm việc', 'huong_ngu' => 'Hướng ngủ', 'nam_nay' => 'Năm :year',
        ]],
        'vat_pham' => ['Đồ vật phong thủy', 'Vật phẩm cát tường, đá quý, linh vật hộ mệnh nên đeo bên người', [
            'da_quy' => 'Đá quý', 'linh_vat' => 'Linh vật', 'trang_suc' => 'Trang sức', 'dat_tai_nha' => 'Đặt tại nhà',
        ]],
        'tuoi_hop' => ['Tuổi hợp làm ăn', 'Tuổi tương sinh để hợp tác, kết giao, tránh tuổi xung khắc', [
            'tam_hop' => 'Tam hợp', 'luc_hop' => 'Lục hợp', 'nen_tranh' => 'Nên tránh', 'hop_menh' => 'Hợp mệnh',
        ]],
        'so_ngay' => ['Số & ngày tốt', 'Con số may mắn, ngày giờ hoàng đạo để khởi sự, ký kết, xuất hành', [
            'so_may_man' => 'Số may mắn', 'ngay_tot' => 'Ngày tốt', 'gio_hoang_dao' => 'Giờ hoàng đạo', 'khoi_su' => 'Khởi sự',
        ]],
        'hoa_giai' => ['Hóa giải sát khí', 'Cách bố trí, trấn yểm hóa giải hung tinh và điểm xấu trong nhà', [
            'bep' => 'Bếp lửa', 'goc_nhon' => 'Góc nhọn', 'cua' => 'Cửa đối cửa', 'guong' => 'Gương',
        ]],
    ];

    /**
     * 12 cung tướng mặt theo thứ tự hiển thị: khoá trong JSON của Gemini => [tên cung, vị trí trên mặt].
     */
    private const PALACES = [
        'menh' => ['Mệnh cung', 'Ấn đường'],
        'tai_bach' => ['Tài bạch cung', 'Mũi'],
        'huynh_de' => ['Huynh đệ cung', 'Lông mày'],
        'dien_trach' => ['Điền trạch cung', 'Mí mắt trên'],
        'tu_tuc' => ['Tử tức cung', 'Dưới mắt (lệ đường)'],
        'no_boc' => ['Nô bộc cung', 'Hai bên cằm'],
        'phu_the' => ['Phu thê cung', 'Đuôi mắt (gian môn)'],
        'tat_ach' => ['Tật ách cung', 'Sơn căn'],
        'thien_di' => ['Thiên di cung', 'Hai góc trán'],
        'quan_loc' => ['Quan lộc cung', 'Giữa trán'],
        'phuc_duc' => ['Phúc đức cung', 'Trên đuôi mày'],
        'phu_mau' => ['Phụ mẫu cung', 'Nhật giác - Nguyệt giác'],
    ];

    /**
     * Mức đánh giá một cung; view tô màu theo đúng các nhãn này.
     */
    private const VERDICTS = ['Cát', 'Trung bình', 'Cần lưu ý'];

    /**
     * Các tab cố định của phần luận theo bộ vị: khoá trong JSON của Gemini => [tiêu đề, phụ đề].
     */
    private const ZONES = [
        'tam_dinh' => [
            'thuong' => ['Thượng đình', '15–30 tuổi'],
            'trung' => ['Trung đình', '31–50 tuổi'],
            'ha' => ['Hạ đình', '51+ tuổi'],
        ],
        'ngu_quan' => [
            'long_may' => ['Lông mày', 'Bảo thọ quan'],
            'mat' => ['Mắt', 'Giám sát quan'],
            'tai' => ['Tai', 'Thái thính quan'],
            'mui' => ['Mũi', 'Thẩm biện quan'],
            'mieng' => ['Miệng', 'Xuất nạp quan'],
        ],
    ];

    private const TEXT = ['type' => 'string'];

    private const TEXTS = ['type' => 'array', 'items' => self::TEXT];

    public function __construct(private Gemini $gemini) {}

    public static function supports(AnalysisSection $section): bool
    {
        return in_array($section, self::SUPPORTED, true);
    }

    public function model(): string
    {
        return $this->gemini->model();
    }

    /**
     * `$onPreview` nhận nội dung đang viết dở (đủ khoá để view hiển thị được) mỗi khi Gemini stream thêm chữ.
     *
     * @param  (Closure(array<string, mixed>): void)|null  $onPreview
     * @return array<string, mixed>
     *
     * @throws RuntimeException khi Gemini lỗi hoặc trả về nội dung sai cấu trúc.
     */
    public function generate(FaceAnalysis $analysis, AnalysisSection $section, ?Closure $onPreview = null): array
    {
        $chart = BirthChart::fromDate($analysis->birth_date, $analysis->birth_hour, $analysis->birth_minute);
        $onText = $onPreview === null ? null : fn (string $text) => $onPreview(self::preview($section, PartialJson::decode($text)));

        return match ($section) {
            AnalysisSection::Overview => $this->overview($analysis, $chart, $onText),
            AnalysisSection::NguHanh => $this->nguHanh($analysis, $chart, $onText),
            AnalysisSection::ThanKhiSac => $this->thanKhiSac($analysis, $chart, $onText),
            AnalysisSection::TamDinh => $this->tamDinh($analysis, $chart, $onText),
            AnalysisSection::NguQuan => $this->nguQuan($analysis, $chart, $onText),
            AnalysisSection::ThapNhiCung => $this->thapNhiCung($analysis, $chart, $onText),
            AnalysisSection::PhongThuy => $this->phongThuy($analysis, $chart, $onText),
            AnalysisSection::Advice => $this->advice($analysis, $chart, $onText),
            default => throw new InvalidArgumentException("Chưa hỗ trợ sinh nội dung cho phần {$section->value}."),
        };
    }

    /**
     * Nội dung đang viết dở → đủ khoá và đúng kiểu cho view; mục nào chưa có thì để trống.
     *
     * @return array<string, mixed>
     */
    public static function preview(AnalysisSection $section, mixed $partial): array
    {
        $partial = is_array($partial) ? $partial : [];
        $strings = fn (mixed $values): array => array_values(array_filter(is_array($values) ? $values : [], 'is_string'));
        $text = fn (mixed $value): string => is_string($value) ? $value : '';

        $rating = fn (mixed $value): int => is_int($value) ? max(0, min(5, $value)) : 0;
        $blocks = fn (mixed $values): array => array_values(array_map(fn (array $block): array => [
            'title' => $text($block['title'] ?? null),
            'items' => array_values(array_map(
                fn (array $item): array => filled($item['label'] ?? null) && is_string($item['label'])
                    ? ['label' => $item['label'], 'text' => $text($item['text'] ?? null)]
                    : ['text' => $text($item['text'] ?? null)],
                array_filter(is_array($block['items'] ?? null) ? $block['items'] : [], 'is_array'),
            )),
        ], array_filter(is_array($values) ? $values : [], 'is_array')));

        if ($section === AnalysisSection::Overview) {
            return ['destiny' => $strings($partial['destiny'] ?? null)];
        }

        if ($section === AnalysisSection::ThanKhiSac) {
            return [
                'tags' => $strings($partial['tags'] ?? null),
                'summary' => $text($partial['summary'] ?? null),
                'rating' => $rating($partial['rating'] ?? null),
                'blocks' => $blocks($partial['blocks'] ?? null),
            ];
        }

        $zones = is_array($partial['zones'] ?? null) ? $partial['zones'] : [];
        $tabs = fn (): array => self::tabs($section, fn (string $key): array => [
            'blocks' => $blocks($zones[$key]['blocks'] ?? null),
            'conclusion' => $text($zones[$key]['conclusion'] ?? null),
        ]);

        if ($section === AnalysisSection::TamDinh) {
            $ratios = is_array($partial['ratios'] ?? null) ? $partial['ratios'] : [];

            return [
                'tags' => $strings($partial['tags'] ?? null),
                'ratios' => array_map(fn (int $index): int => is_int($ratios[$index] ?? null) ? $ratios[$index] : 0, [0, 1, 2]),
                'summary' => $strings($partial['summary'] ?? null),
                'rating' => $rating($partial['rating'] ?? null),
                'tabs' => $tabs(),
            ];
        }

        if ($section === AnalysisSection::NguQuan) {
            return [
                'tags' => $strings($partial['tags'] ?? null),
                'summary' => $strings($partial['summary'] ?? null),
                'tabs' => $tabs(),
            ];
        }

        if (isset(self::CARDS[$section->value])) {
            $groups = is_array($partial['blocks'] ?? null) ? $partial['blocks'] : [];

            // Chỉ giữ các mục đã có chữ, để thẻ hiện dần theo nội dung đang viết.
            return [
                'tags' => $strings($partial['tags'] ?? null),
                'summary' => $text($partial['summary'] ?? null),
                'blocks' => array_values(array_filter(
                    array_map(fn (array $block): array => [...$block, 'items' => array_values(array_filter($block['items'], fn (array $row): bool => filled($row['text'])))],
                        self::cards($section, fn (string $group, string $key): string => $text($groups[$group][$key] ?? null))),
                    fn (array $block): bool => $block['items'] !== [],
                )),
            ];
        }

        if ($section === AnalysisSection::ThapNhiCung) {
            $palaces = is_array($partial['palaces'] ?? null) ? $partial['palaces'] : [];

            return [
                'tags' => $strings($partial['tags'] ?? null),
                'summary' => $text($partial['summary'] ?? null),
                'palaces' => self::palaces(fn (string $key): array => [
                    'verdict' => in_array($palaces[$key]['verdict'] ?? null, self::VERDICTS, true) ? $palaces[$key]['verdict'] : '',
                    'text' => $text($palaces[$key]['text'] ?? null),
                    'analysis' => $text($palaces[$key]['analysis'] ?? null),
                    'advice' => $text($palaces[$key]['advice'] ?? null),
                ]),
            ];
        }

        $elementKeys = array_map(fn (Element $element): string => $element->value, Element::cases());
        $radar = is_array($partial['radar'] ?? null) ? $partial['radar'] : [];

        return [
            'tags' => $strings($partial['tags'] ?? null),
            'radar' => array_combine($elementKeys, array_map(fn (string $key): int => is_int($radar[$key] ?? null) ? $radar[$key] : 0, $elementKeys)),
            'summary' => $text($partial['summary'] ?? null),
            'strengths' => $strings($partial['strengths'] ?? null),
            'cautions' => $strings($partial['cautions'] ?? null),
            'blocks' => $blocks($partial['blocks'] ?? null),
            'balance' => array_values(array_map(
                fn (array $tip): array => ['element' => $tip['element'], 'title' => $text($tip['title'] ?? null), 'hint' => $text($tip['hint'] ?? null)],
                array_filter(is_array($partial['balance'] ?? null) ? $partial['balance'] : [], fn (mixed $tip): bool => is_array($tip) && in_array($tip['element'] ?? null, $elementKeys, true)),
            )),
        ];
    }

    /**
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function overview(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $destiny = $chart->destinyElement();

        $prompt = $this->profile($analysis, $chart)."\n\n".<<<PROMPT
            Hãy luận giải TỔNG QUAN BÁT TỰ VÀ BẢN MỆNH:
            - score: điểm vận khí tổng thể 0-100 (thường 60-90), dựa trên sự cân bằng ngũ hành trong bát tự và quan hệ với bản mệnh.
            - verdict: nhận định ngắn 3-6 chữ ứng với điểm (VD: "Vận khí khá tốt").
            - ratios: tỷ lệ % các yếu tố Cát (good), Trung bình (neutral), Cần lưu ý (caution), tổng đúng 100.
            - destiny: đúng 2 đoạn văn, mỗi đoạn 2-3 câu. Đoạn 1 được hiển thị ngay sau câu
              "{$chart->destiny()} - mệnh {$destiny->label()}, được {$destiny->generatedBy()->label()} sinh và sinh ra {$destiny->generates()->label()}."
              nên viết tiếp mạch đó (khí chất, tính cách theo nạp âm), không lặp lại câu này.
              Đoạn 2 phân tích cấu trúc bát tự: hành vượng, hành thiếu, tương sinh tương khắc với bản mệnh và tính cách chủ đạo.
            PROMPT;

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'score' => ['type' => 'integer'],
                'verdict' => ['type' => 'string'],
                'ratios' => [
                    'type' => 'object',
                    'properties' => ['good' => ['type' => 'integer'], 'neutral' => ['type' => 'integer'], 'caution' => ['type' => 'integer']],
                    'required' => ['good', 'neutral', 'caution'],
                ],
                'destiny' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['score', 'verdict', 'ratios', 'destiny'],
            // Đoạn văn trước để khách đọc được ngay khi stream, các con số sau.
            'propertyOrdering' => ['destiny', 'verdict', 'score', 'ratios'],
        ], onText: $onText);

        $this->validate($content, [
            'score' => ['required', 'integer', 'between:0,100'],
            'verdict' => ['required', 'string'],
            'ratios.good' => ['required', 'integer', 'between:0,100'],
            'ratios.neutral' => ['required', 'integer', 'between:0,100'],
            'ratios.caution' => ['required', 'integer', 'between:0,100'],
            'destiny' => ['required', 'array', 'min:1'],
            'destiny.*' => ['required', 'string'],
        ]);

        return [
            ...Arr::only($content, ['score', 'verdict', 'destiny']),
            'ratios' => $this->toPercentages(Arr::only($content['ratios'], ['good', 'neutral', 'caution'])),
        ];
    }

    /**
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function nguHanh(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $elements = implode(', ', array_map(fn (Element $element): string => "{$element->value} = {$element->label()}", Element::cases()));
        $ratios = $analysis->face_metrics['ratios'] ?? null;
        $measured = $ratios ? "\nSố đo Tam đình từ ảnh (thượng - trung - hạ đình): {$ratios[0]}% - {$ratios[1]}% - {$ratios[2]}%." : '';

        $prompt = $this->profile($analysis, $chart).$measured."\n\n".<<<PROMPT
            Dựa vào ẢNH CHÂN DUNG gửi kèm, hãy luận giải NGŨ HÀNH TƯỚNG PHÁP (khuôn mặt thuộc hành nào):
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Kim vượng", "Thủy tương sinh").
            - radar: tỷ lệ % từng hành thể hiện trên khuôn mặt (khóa: {$elements}), tổng đúng 100.
            - summary: 2-3 câu tóm tắt hình tướng và hành chủ đạo.
            - strengths: 3-4 ưu điểm, cautions: 2-3 điều cần lưu ý; mỗi ý 2-6 chữ.
            - blocks: 2-3 khối luận giải chi tiết. title dạng "Hình dáng khuôn mặt: mặt hình Mộc pha Kim", "Khí sắc và nước da"...
              Mỗi khối 2-4 items; item có label là bộ vị (VD: "Khung xương và Trán", "Xương gò má và Cằm (Địa khố)") và text 2-4 câu
              mô tả đặc điểm nhìn thấy trên ảnh rồi luận giải ý nghĩa. Item đầu của khối đầu có thể bỏ label để làm câu dẫn.
            - balance: đúng 3 gợi ý cân bằng ngũ hành; element là khóa hành, title dạng "Tăng Hỏa"/"Giảm Kim", hint là gợi ý ngắn về màu sắc hoặc vật phẩm.
            Kết hợp với bản mệnh trong bát tự khi luận về tương sinh, tương khắc.
            PROMPT;

        $elementKeys = array_map(fn (Element $element): string => $element->value, Element::cases());
        $text = self::TEXT;
        $texts = self::TEXTS;

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => $texts,
                'radar' => [
                    'type' => 'object',
                    'properties' => array_fill_keys($elementKeys, ['type' => 'integer']),
                    'required' => $elementKeys,
                ],
                'summary' => $text,
                'strengths' => $texts,
                'cautions' => $texts,
                'blocks' => self::blocksSchema(),
                'balance' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['element' => ['type' => 'string', 'enum' => $elementKeys], 'title' => $text, 'hint' => $text],
                    'required' => ['element', 'title', 'hint'],
                ]],
            ],
            'required' => ['tags', 'radar', 'summary', 'strengths', 'cautions', 'blocks', 'balance'],
            'propertyOrdering' => ['tags', 'radar', 'summary', 'strengths', 'cautions', 'blocks', 'balance'],
        ], [$this->portrait($analysis)], $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            ...array_fill_keys(array_map(fn (string $key): string => "radar.{$key}", $elementKeys), ['required', 'integer', 'between:0,100']),
            'summary' => ['required', 'string'],
            'strengths' => ['required', 'array'],
            'strengths.*' => ['string'],
            'cautions' => ['required', 'array'],
            'cautions.*' => ['string'],
            ...self::blocksRules('blocks'),
            'balance' => ['required', 'array', 'min:1'],
            'balance.*.element' => ['required', 'in:'.implode(',', $elementKeys)],
            'balance.*.title' => ['required', 'string'],
            'balance.*.hint' => ['required', 'string'],
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'radar' => $this->toPercentages(Arr::only($content['radar'], $elementKeys)),
            'summary' => $content['summary'],
            'strengths' => $content['strengths'],
            'cautions' => $content['cautions'],
            'blocks' => $this->blocks($content['blocks']),
            'balance' => array_map(fn (array $tip): array => Arr::only($tip, ['element', 'title', 'hint']), array_slice($content['balance'], 0, 3)),
        ];
    }

    /**
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function thanKhiSac(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $prompt = $this->profile($analysis, $chart)."\n\n".<<<'PROMPT'
            Dựa vào ẢNH CHÂN DUNG gửi kèm, hãy luận giải TỔNG QUAN THẦN - KHÍ - SẮC:
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Thần thái tốt", "Khí sắc ổn định").
            - summary: 2-3 câu tóm tắt thần thái, khí chất và sắc diện.
            - rating: đánh giá tổng thể 1-5 sao (thường 3-4; 5 chỉ khi cả ba yếu tố đều rất tốt).
            - blocks: đúng 4 khối, title lần lượt:
              "1. Về Thần (Tinh thần, Ý chí và Thần thái)", "2. Về Khí (Sinh khí, Năng lượng và Hơi thở sống)",
              "3. Về Sắc (Sắc diện, Màu sắc da và Vận hạn ngắn hạn)", "Tổng kết".
              Ba khối đầu mỗi khối 2 items: label "Biểu hiện" (2-3 câu mô tả đặc điểm nhìn thấy trên ảnh: ánh mắt, nét mặt, tư thế, màu da)
              và label "Luận giải" (2-4 câu ý nghĩa theo nhân tướng). Khối "Tổng kết" có 1 item không có label, 2-3 câu.
            PROMPT;

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => self::TEXTS,
                'summary' => self::TEXT,
                'rating' => ['type' => 'integer'],
                'blocks' => self::blocksSchema(),
            ],
            'required' => ['tags', 'summary', 'rating', 'blocks'],
            'propertyOrdering' => ['tags', 'summary', 'rating', 'blocks'],
        ], [$this->portrait($analysis)], $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'summary' => ['required', 'string'],
            'rating' => ['required', 'integer', 'between:1,5'],
            ...self::blocksRules('blocks'),
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'summary' => $content['summary'],
            'rating' => $content['rating'],
            'blocks' => $this->blocks($content['blocks']),
        ];
    }

    /**
     * Tỷ lệ tam đình lấy theo số đo thật từ ảnh (FaceDetector) nếu có; chưa đo được thì để Gemini ước lượng.
     *
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function tamDinh(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $measured = $analysis->face_metrics['ratios'] ?? null;
        $ratiosHint = $measured
            ? "- ratios: dùng đúng số đo từ ảnh [{$measured[0]}, {$measured[1]}, {$measured[2]}] (% thượng - trung - hạ đình)."
            : '- ratios: ước lượng tỷ lệ % chiều dài thượng - trung - hạ đình trên ảnh, 3 số nguyên tổng đúng 100.';

        $prompt = $this->profile($analysis, $chart)."\n\n".<<<PROMPT
            Dựa vào ẢNH CHÂN DUNG gửi kèm, hãy luận giải TAM ĐÌNH (thượng đình: chân tóc đến chân mày, vận 15-30 tuổi;
            trung đình: chân mày đến chóp mũi, vận 31-50 tuổi; hạ đình: dưới mũi đến cằm, vận từ 51 tuổi):
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Trung đình tốt", "Hậu vận ổn").
            {$ratiosHint}
            - summary: đúng 2 đoạn, mỗi đoạn 1-3 câu. Đoạn 1 nhận định chung về tam đình;
              đoạn 2 so sánh tỷ lệ ba phần (nêu số %) và giai đoạn nào thuận lợi hơn.
            - rating: đánh giá tổng thể 1-5 sao (thường 3-4).
            - zones: luận giải từng đình với khoá thuong, trung, ha. Mỗi đình có:
              blocks: 1-3 khối, title là bộ vị (VD: "1. Vầng trán (Thiên đình)", "2. Ấn đường", "1. Cằm và Địa khố");
              mỗi khối 2-3 items có label (VD: "Đặc điểm" mô tả điều nhìn thấy trên ảnh, "Luận giải", hoặc tên cung như "Cung Quan Lộc") và text 2-4 câu.
              conclusion: 1-2 câu tổng kết về đình đó, có liên hệ với bản mệnh.
            PROMPT;

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => self::TEXTS,
                'ratios' => ['type' => 'array', 'items' => ['type' => 'integer']],
                'summary' => self::TEXTS,
                'rating' => ['type' => 'integer'],
                'zones' => self::zonesSchema(AnalysisSection::TamDinh),
            ],
            'required' => ['tags', 'ratios', 'summary', 'rating', 'zones'],
            'propertyOrdering' => ['tags', 'ratios', 'summary', 'rating', 'zones'],
        ], [$this->portrait($analysis)], $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'ratios' => ['required', 'array', 'size:3'],
            'ratios.*' => ['required', 'integer', 'between:0,100'],
            'summary' => ['required', 'array', 'min:1'],
            'summary.*' => ['required', 'string'],
            'rating' => ['required', 'integer', 'between:1,5'],
            ...self::zonesRules(AnalysisSection::TamDinh),
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'ratios' => $measured ?? $this->toPercentages(array_values($content['ratios'])),
            'summary' => $content['summary'],
            'rating' => $content['rating'],
            'tabs' => $this->zonesToTabs(AnalysisSection::TamDinh, $content['zones']),
        ];
    }

    /**
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function nguQuan(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $prompt = $this->profile($analysis, $chart)."\n\n".<<<'PROMPT'
            Dựa vào ẢNH CHÂN DUNG gửi kèm, hãy luận giải NGŨ QUAN (lông mày - Bảo thọ quan, mắt - Giám sát quan,
            tai - Thái thính quan, mũi - Thẩm biện quan, miệng - Xuất nạp quan):
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Mắt có thần", "Mũi giữ tài").
            - summary: đúng 2 đoạn. Đoạn 1 nhận định chung trong 1-2 câu;
              đoạn 2 (2-4 câu) nêu quan nào nổi bật nhất, quan nào cần lưu ý và ảnh hưởng tổng thể.
            - zones: luận giải từng quan với khoá long_may, mat, tai, mui, mieng. Mỗi quan có:
              blocks: 1-2 khối, title là đặc điểm hoặc cung liên quan (VD: "1. Hình dáng và Độ dài mày", "2. Cung Huynh Đệ",
              "1. Sống mũi và Chuẩn đầu", "2. Cung Điền Trạch và Cung Phu Thê");
              mỗi khối 1-3 items có label (VD: "Đặc điểm" mô tả điều nhìn thấy trên ảnh, "Luận giải", hoặc tên cung) và text 2-4 câu.
              Tai thường bị tóc che hoặc khuất góc chụp: nếu không thấy rõ thì nói thẳng là không quan sát được và chỉ luận ở mức chung.
              conclusion: 1-2 câu tổng kết về quan đó.
            PROMPT;

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => self::TEXTS,
                'summary' => self::TEXTS,
                'zones' => self::zonesSchema(AnalysisSection::NguQuan),
            ],
            'required' => ['tags', 'summary', 'zones'],
            'propertyOrdering' => ['tags', 'summary', 'zones'],
        ], [$this->portrait($analysis)], $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'summary' => ['required', 'array', 'min:1'],
            'summary.*' => ['required', 'string'],
            ...self::zonesRules(AnalysisSection::NguQuan),
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'summary' => $content['summary'],
            'tabs' => $this->zonesToTabs(AnalysisSection::NguQuan, $content['zones']),
        ];
    }

    /**
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function thapNhiCung(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $palaces = collect(self::PALACES)->map(fn (array $palace, string $key): string => "{$key} = {$palace[0]} ({$palace[1]})")->implode('; ');
        $verdicts = implode('", "', self::VERDICTS);

        $prompt = $this->profile($analysis, $chart)."\n\n".<<<PROMPT
            Dựa vào ẢNH CHÂN DUNG gửi kèm, hãy luận giải THẬP NHỊ CUNG trên khuôn mặt.
            12 cung (khoá = tên cung (vị trí)): {$palaces}.
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Quan lộc sáng", "Tài bạch vững").
            - summary: 2-3 câu nhận định chung, nêu các cung tốt nhất và cung cần lưu tâm.
            - palaces: đủ 12 cung theo khoá trên. Mỗi cung có:
              verdict: một trong "{$verdicts}" (đánh giá thực tế theo ảnh, không cho tất cả đều "Cát");
              text: 1 câu ngắn tóm tắt (dưới 15 chữ);
              analysis: 2-3 câu, mô tả đặc điểm nhìn thấy ở vị trí đó trên ảnh rồi luận ý nghĩa của cung;
              advice: 1-2 câu lời khuyên thực tế.
              Vùng nào bị tóc, kính hoặc góc chụp che khuất thì nói rõ là không quan sát được và đánh giá "Trung bình".
            PROMPT;

        $keys = array_keys(self::PALACES);
        $palace = [
            'type' => 'object',
            'properties' => [
                'verdict' => ['type' => 'string', 'enum' => self::VERDICTS],
                'text' => self::TEXT,
                'analysis' => self::TEXT,
                'advice' => self::TEXT,
            ],
            'required' => ['verdict', 'text', 'analysis', 'advice'],
            'propertyOrdering' => ['verdict', 'text', 'analysis', 'advice'],
        ];

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => self::TEXTS,
                'summary' => self::TEXT,
                'palaces' => ['type' => 'object', 'properties' => array_fill_keys($keys, $palace), 'required' => $keys, 'propertyOrdering' => $keys],
            ],
            'required' => ['tags', 'summary', 'palaces'],
            'propertyOrdering' => ['tags', 'summary', 'palaces'],
        ], [$this->portrait($analysis)], $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'summary' => ['required', 'string'],
            ...collect($keys)->flatMap(fn (string $key): array => [
                "palaces.{$key}.verdict" => ['required', 'in:'.implode(',', self::VERDICTS)],
                "palaces.{$key}.text" => ['required', 'string'],
                "palaces.{$key}.analysis" => ['required', 'string'],
                "palaces.{$key}.advice" => ['required', 'string'],
            ])->all(),
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'summary' => $content['summary'],
            'palaces' => self::palaces(fn (string $key): array => Arr::only($content['palaces'][$key], ['verdict', 'text', 'analysis', 'advice'])),
        ];
    }

    /**
     * Quan hệ tuổi (tam hợp, lục hợp, lục xung, lục hại) tính từ địa chi năm sinh âm lịch, để Gemini không tự suy ra sai.
     *
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function phongThuy(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $branch = $chart->lunarDate->yearPillar()->branch;
        $branchName = fn (int $offset): string => Pillar::BRANCHES[Pillar::mod($offset, 12)];
        $year = now()->year;
        $destiny = $chart->destinyElement();
        $nguHanh = $analysis->section(AnalysisSection::NguHanh)?->content['radar'] ?? null;
        $faceElements = $nguHanh
            ? "\nTỷ lệ ngũ hành trên khuôn mặt: ".collect($nguHanh)->map(fn (int $value, string $element): string => Element::from($element)->label()." {$value}%")->implode(', ').'.'
            : '';

        $prompt = $this->profile($analysis, $chart).$faceElements."\n\n".<<<PROMPT
            Hãy đưa ra chỉ dẫn PHONG THỦY ỨNG DỤNG theo bản mệnh {$chart->destiny()} (hành {$destiny->label()};
            {$destiny->generatedBy()->label()} sinh {$destiny->label()}, {$destiny->label()} sinh {$destiny->generates()->label()}).
            Quan hệ tuổi {$branchName($branch)} (dùng đúng, không tự tính lại): tam hợp {$branchName($branch + 4)}, {$branchName($branch + 8)};
            lục hợp {$branchName(1 - $branch)}; lục xung {$branchName($branch + 6)}; lục hại {$branchName(7 - $branch)}.
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Hợp hướng Tây", "Dùng Thổ sinh Kim").
            - summary: 2-3 câu: hành cần bổ trợ, hành cần hạn chế (dựa trên bản mệnh và hành vượng/thiếu trong bát tự) và lợi ích khi áp dụng.
            - blocks: mỗi mục là 1 cụm ngắn 5-15 chữ, liệt kê cụ thể, có thể kèm lý do trong ngoặc (VD: "Vàng, nâu đất (Thổ sinh Kim)").
              mau_sac: ban_menh (màu của hành bản mệnh), tuong_sinh (màu hành sinh ra bản mệnh), nen_tranh (màu hành khắc bản mệnh).
              huong: huong_nha, ban_lam_viec, huong_ngu, nam_nay (gợi ý hướng/vị trí tốt riêng cho năm {$year}; nhãn đã ghi năm nên không nhắc lại năm).
              vat_pham: da_quy, linh_vat, trang_suc, dat_tai_nha.
              tuoi_hop: tam_hop, luc_hop (nêu tên tuổi kèm ý nghĩa ngắn), nen_tranh (tuổi xung, hại), hop_menh (người mệnh nào hợp).
              so_ngay: so_may_man, ngay_tot, gio_hoang_dao (kèm khung giờ), khoi_su (mùa/tháng thuận lợi).
              hoa_giai: bep, goc_nhon, cua, guong (cách bố trí hoặc hóa giải thực tế trong nhà).
            Chỉ đưa lời khuyên thực tế, dễ làm; không khuyên mua vật phẩm đắt tiền hay cúng bái mê tín.
            PROMPT;

        return $this->cardsContent(AnalysisSection::PhongThuy, $prompt, $onText);
    }

    /**
     * Quan hệ của 10 năm tới với tuổi khách được tính sẵn (năm tuổi, xung, hại, tam hợp, lục hợp) để Gemini chọn năm tốt/xấu.
     *
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function advice(FaceAnalysis $analysis, BirthChart $chart, ?Closure $onText): array
    {
        $branch = $chart->lunarDate->yearPillar()->branch;
        $relations = [0 => 'năm tuổi (phạm Thái tuế)', 6 => 'lục xung', 4 => 'tam hợp', 8 => 'tam hợp'];
        $age = $chart->age();
        $current = $age.'-'.($age + 9);
        $next = ($age + 10).'-'.($age + 19);

        $years = collect(range(now()->year + 1, now()->year + 10))->map(function (int $year) use ($branch, $relations): string {
            $pillar = Pillar::ofYear($year);
            $relation = $relations[Pillar::mod($pillar->branch - $branch, 12)]
                ?? match (Pillar::mod($pillar->branch, 12)) {
                    Pillar::mod(1 - $branch, 12) => 'lục hợp',
                    Pillar::mod(7 - $branch, 12) => 'lục hại',
                    default => null,
                };

            return "{$year} {$pillar->name()} (hành {$pillar->branchElement()->label()}".($relation ? ", {$relation}" : '').')';
        })->implode('; ');

        $cautions = collect($analysis->section(AnalysisSection::ThapNhiCung)?->content['palaces'] ?? [])
            ->where('verdict', 'Cần lưu ý')
            ->map(fn (array $palace): string => "{$palace['name']} ({$palace['text']})")
            ->implode('; ');

        $prompt = $this->profile($analysis, $chart).($cautions ? "\nCác cung cần lưu ý trên khuôn mặt: {$cautions}." : '')."\n\n".<<<PROMPT
            Hãy đưa ra LỜI KHUYÊN CẢI VẬN thiết thực cho quý chủ (năm nay {$age} tuổi mụ).
            Các năm sắp tới và quan hệ với tuổi quý chủ (dùng đúng, không tự tính lại): {$years}.
            - tags: 2 nhãn ngắn 2-3 chữ (VD: "Tu tâm dưỡng tính", "Chọn thời khởi sự").
            - summary: 2-3 câu: điểm mạnh cần phát huy, điểm yếu cần hóa giải theo bản mệnh và bát tự, tinh thần chung của việc cải vận.
            - blocks: mỗi mục là 1 câu ngắn 8-20 chữ, cụ thể và làm được.
              tam_phap: tu_tam (thói quen tâm tính nên rèn theo khí chất bản mệnh), thien_dinh (cách tĩnh tâm, thời lượng), hanh_thien (việc thiện phù hợp).
              hanh_dong: hien_tai (trọng tâm giai đoạn {$current} tuổi, mở đầu bằng khoảng tuổi), muoi_nam_toi (trọng tâm giai đoạn {$next} tuổi, mở đầu bằng khoảng tuổi),
                nen_lam, nen_tranh (thói quen, quyết định tài chính, quan hệ).
              thoi_diem: nam_tot (1-2 năm thuận lợi trong danh sách trên, ghi kèm can chi), thang_tot (tháng âm lịch), ngay_gio (ngày theo địa chi và giờ kèm khung giờ),
                than_trong (năm cần thận trọng trong danh sách trên, ghi kèm can chi và lý do).
              vat_pham: linh_vat, deo_ben_nguoi, huong_nha, bo_tri (đơn giản, chi phí thấp).
            Không hứa hẹn kết quả chắc chắn, không khuyên cúng bái, giải hạn tốn kém hay mua vật phẩm đắt tiền.
            PROMPT;

        return $this->cardsContent(AnalysisSection::Advice, $prompt, $onText);
    }

    /**
     * Gọi Gemini cho phần dạng thẻ (CARDS): tags, summary và nội dung từng mục theo khoá nhóm/khoá mục.
     *
     * @param  (Closure(string): void)|null  $onText
     * @return array<string, mixed>
     */
    private function cardsContent(AnalysisSection $section, string $prompt, ?Closure $onText): array
    {
        $groups = self::CARDS[$section->value];
        $groupSchema = fn (array $items): array => [
            'type' => 'object',
            'properties' => array_fill_keys(array_keys($items), self::TEXT),
            'required' => array_keys($items),
            'propertyOrdering' => array_keys($items),
        ];

        $content = $this->gemini->json($this->instruction(), $prompt, [
            'type' => 'object',
            'properties' => [
                'tags' => self::TEXTS,
                'summary' => self::TEXT,
                'blocks' => [
                    'type' => 'object',
                    'properties' => array_map(fn (array $group): array => $groupSchema($group[2]), $groups),
                    'required' => array_keys($groups),
                    'propertyOrdering' => array_keys($groups),
                ],
            ],
            'required' => ['tags', 'summary', 'blocks'],
            'propertyOrdering' => ['tags', 'summary', 'blocks'],
        ], onText: $onText);

        $this->validate($content, [
            'tags' => ['required', 'array'],
            'tags.*' => ['string'],
            'summary' => ['required', 'string'],
            ...collect($groups)->flatMap(fn (array $group, string $key): array => collect(array_keys($group[2]))
                ->mapWithKeys(fn (string $item): array => ["blocks.{$key}.{$item}" => ['required', 'string']])
                ->all())->all(),
        ]);

        return [
            'tags' => array_slice($content['tags'], 0, 3),
            'summary' => $content['summary'],
            'blocks' => self::cards($section, fn (string $group, string $key): string => $content['blocks'][$group][$key]),
        ];
    }

    /**
     * Các nhóm thẻ theo thứ tự cố định, dạng block-cards (tiêu đề, mô tả, các dòng nhãn - nội dung).
     *
     * @param  Closure(string, string): string  $text  Nội dung theo (khoá nhóm, khoá mục).
     * @return list<array{title: string, description: string, items: list<array{label: string, text: string}>}>
     */
    private static function cards(AnalysisSection $section, Closure $text): array
    {
        return collect(self::CARDS[$section->value])->map(fn (array $group, string $key): array => [
            'title' => $group[0],
            'description' => $group[1],
            'items' => collect($group[2])->map(fn (string $label, string $item): array => [
                'label' => str_replace(':year', (string) now()->year, $label),
                'text' => $text($key, $item),
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * 12 cung theo thứ tự cố định, kèm tên cung và vị trí trên mặt.
     *
     * @param  Closure(string): array{verdict: string, text: string, analysis: string, advice: string}  $palace  Nội dung theo khoá cung.
     * @return list<array{name: string, position: string, verdict: string, text: string, analysis: string, advice: string}>
     */
    private static function palaces(Closure $palace): array
    {
        return array_map(function (string $key) use ($palace): array {
            [$name, $position] = self::PALACES[$key];

            return ['name' => $name, 'position' => $position, ...$palace($key)];
        }, array_keys(self::PALACES));
    }

    /**
     * Các tab cố định (tiêu đề, phụ đề theo ZONES) của một phần; tổng kết trống thì bỏ.
     *
     * @param  Closure(string): array{blocks: list<array<string, mixed>>, conclusion: string}  $zone  Nội dung theo khoá tab.
     * @return list<array<string, mixed>>
     */
    private static function tabs(AnalysisSection $section, Closure $zone): array
    {
        return array_map(function (string $key) use ($section, $zone): array {
            [$title, $subtitle] = self::ZONES[$section->value][$key];
            ['blocks' => $blocks, 'conclusion' => $conclusion] = $zone($key);
            $tab = ['title' => $title, 'subtitle' => $subtitle, 'blocks' => $blocks];

            return filled($conclusion) ? [...$tab, 'conclusion' => ['title' => "Tổng kết về {$title}", 'text' => $conclusion]] : $tab;
        }, array_keys(self::ZONES[$section->value]));
    }

    /**
     * @param  array<string, array<string, mixed>>  $zones
     * @return list<array<string, mixed>>
     */
    private function zonesToTabs(AnalysisSection $section, array $zones): array
    {
        return self::tabs($section, fn (string $key): array => [
            'blocks' => $this->blocks($zones[$key]['blocks']),
            'conclusion' => $zones[$key]['conclusion'] ?? '',
        ]);
    }

    /**
     * Schema `zones`: mỗi khoá tab gồm các khối luận giải và một câu tổng kết.
     *
     * @return array<string, mixed>
     */
    private static function zonesSchema(AnalysisSection $section): array
    {
        $keys = array_keys(self::ZONES[$section->value]);
        $zone = [
            'type' => 'object',
            'properties' => ['blocks' => self::blocksSchema(), 'conclusion' => self::TEXT],
            'required' => ['blocks', 'conclusion'],
            'propertyOrdering' => ['blocks', 'conclusion'],
        ];

        return ['type' => 'object', 'properties' => array_fill_keys($keys, $zone), 'required' => $keys, 'propertyOrdering' => $keys];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function zonesRules(AnalysisSection $section): array
    {
        return collect(array_keys(self::ZONES[$section->value]))->flatMap(fn (string $key): array => [
            ...self::blocksRules("zones.{$key}.blocks"),
            "zones.{$key}.conclusion" => ['nullable', 'string'],
        ])->all();
    }

    /**
     * Schema các khối luận giải: tiêu đề và các mục có nhãn (tuỳ chọn) kèm đoạn văn.
     *
     * @return array<string, mixed>
     */
    private static function blocksSchema(): array
    {
        return ['type' => 'array', 'items' => [
            'type' => 'object',
            'properties' => [
                'title' => self::TEXT,
                'items' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['label' => self::TEXT, 'text' => self::TEXT],
                    'required' => ['text'],
                ]],
            ],
            'required' => ['title', 'items'],
        ]];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function blocksRules(string $path): array
    {
        return [
            $path => ['required', 'array', 'min:1'],
            "{$path}.*.title" => ['required', 'string'],
            "{$path}.*.items" => ['required', 'array', 'min:1'],
            "{$path}.*.items.*.label" => ['nullable', 'string'],
            "{$path}.*.items.*.text" => ['required', 'string'],
        ];
    }

    /**
     * Bỏ nhãn rỗng để view hiểu là đoạn không có nhãn.
     *
     * @param  list<array<string, mixed>>  $blocks
     * @return list<array<string, mixed>>
     */
    private function blocks(array $blocks): array
    {
        return array_map(fn (array $block): array => [
            'title' => $block['title'],
            'items' => array_map(fn (array $item): array => filled($item['label'] ?? null)
                ? ['label' => $item['label'], 'text' => $item['text']]
                : ['text' => $item['text']], $block['items']),
        ], $blocks);
    }

    private function instruction(): string
    {
        return <<<'INSTRUCTION'
            Bạn là chuyên gia nhân tướng học và mệnh lý phương Đông của Tâm Minh, viết bản luận giải cá nhân cho khách hàng.
            - Viết tiếng Việt có dấu, giọng văn ngắn gọn, rõ ràng, dễ đọc và khách quan; dùng thuật ngữ nhân tướng/mệnh lý kèm giải thích dễ hiểu.
            - Câu ngắn, mỗi câu một ý, nói thẳng vào nội dung. Không cường điệu, không dùng hình ảnh văn hoa hay từ ngữ phóng đại (VD: "phi thường", "tuyệt vời", "hiếm có", "rực rỡ"), không khen ngợi quá mức.
            - Luôn gọi khách là "quý chủ" (VD: "quý chủ là người...", "Quý chủ nên..."), không dùng "anh", "chị", "bạn", không gọi tên, không xưng "tôi".
            - Nêu cả điểm mạnh và điểm cần lưu ý một cách cân bằng, trung thực; điểm yếu nói nhẹ nhàng kèm cách cải thiện. Không phán về bệnh hiểm nghèo, tai nạn, tuổi thọ hay cái chết.
            - Chỉ dựa trên dữ liệu được cung cấp; với ảnh, chỉ mô tả những đặc điểm thật sự nhìn thấy.
            - Không nhắc đến AI, mô hình hay việc phân tích ảnh bằng máy.
            INSTRUCTION;
    }

    private function profile(FaceAnalysis $analysis, BirthChart $chart): string
    {
        $pillars = collect($chart->pillars())
            ->map(fn ($pillar, string $label): string => "{$label}: ".($pillar ? "{$pillar->name()} ({$pillar->stemElement()->label()} - {$pillar->branchElement()->label()})" : 'chưa rõ giờ sinh'))
            ->implode('; ');

        $counts = collect($chart->elementCounts())
            ->map(fn (int $count, string $element): string => Element::from($element)->label()." {$count}")
            ->implode(', ');

        return implode("\n", array_filter([
            'THÔNG TIN KHÁCH HÀNG',
            "Giới tính: {$analysis->gender->label()}",
            "Ngày sinh dương lịch: {$analysis->birth_date->format('d/m/Y')}".($analysis->birth_time ? " lúc {$analysis->birth_time}" : ' (không rõ giờ sinh)'),
            "Âm lịch: {$chart->lunarDate->format()}",
            "Tuổi mụ năm nay: {$chart->age()}",
            "Bát tự - {$pillars}",
            "Số lần xuất hiện ngũ hành trong bát tự: {$counts}",
            "Mệnh (nạp âm năm sinh): {$chart->destiny()} - hành {$chart->destinyElement()->label()}",
            $chart->cuc() ? "Cục mệnh: {$chart->cuc()}" : null,
            $chart->bodyPalace() ? "Thân cư: {$chart->bodyPalace()}" : null,
        ]));
    }

    /**
     * Ảnh khuôn mặt đã cắt (rõ nét hơn), không có thì dùng ảnh chân dung gốc.
     *
     * @return array{mime_type: string, data: string}
     */
    private function portrait(FaceAnalysis $analysis): array
    {
        $disk = Storage::disk('local');
        $path = $analysis->face_crop_path && $disk->exists($analysis->face_crop_path)
            ? $analysis->face_crop_path
            : $analysis->portrait_path;

        if ($path === null || ! $disk->exists($path)) {
            throw new RuntimeException('Không tìm thấy ảnh chân dung để luận giải.');
        }

        return ['mime_type' => $disk->mimeType($path) ?: 'image/jpeg', 'data' => $disk->get($path)];
    }

    /**
     * @param  array<string, mixed>  $content
     * @param  array<string, mixed>  $rules
     */
    private function validate(array $content, array $rules): void
    {
        $validator = Validator::make($content, $rules);

        if ($validator->fails()) {
            throw new RuntimeException('Gemini trả về nội dung sai cấu trúc: '.$validator->errors()->first());
        }
    }

    /**
     * Chuẩn hoá các giá trị về tỷ lệ % có tổng đúng 100 (phần dư dồn vào giá trị lớn nhất).
     *
     * @param  array<string, int>  $values
     * @return array<string, int>
     */
    private function toPercentages(array $values): array
    {
        $total = array_sum($values);

        if ($total <= 0) {
            return array_map(fn (): int => intdiv(100, count($values)), $values);
        }

        $percentages = array_map(fn (int $value): int => (int) round($value * 100 / $total), $values);
        $percentages[array_search(max($percentages), $percentages, true)] += 100 - array_sum($percentages);

        return $percentages;
    }
}
