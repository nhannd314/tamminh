<?php

namespace Database\Seeders;

use App\FiveElement;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Nhóm hàng và vật phẩm phong thủy mẫu. Chạy lại nhiều lần không tạo trùng (khớp theo slug).
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Linh vật', 'slug' => 'linh-vat', 'sort' => 1],
            ['name' => 'Vòng tay', 'slug' => 'vong-tay', 'sort' => 2],
            ['name' => 'Đá phong thủy', 'slug' => 'da-phong-thuy', 'sort' => 3],
            ['name' => 'Tranh & thư pháp', 'slug' => 'tranh-thu-phap', 'sort' => 4],
            ['name' => 'Cây phong thủy', 'slug' => 'cay-phong-thuy', 'sort' => 5],
        ])->mapWithKeys(fn (array $category): array => [
            $category['slug'] => ProductCategory::query()->updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        foreach ($this->products() as $sort => $product) {
            Product::query()->updateOrCreate(['slug' => $product['slug']], [
                'stock' => 14,
                ...Arr::except($product, 'category'),
                'product_category_id' => $categories[$product['category']]->getKey(),
                'sort' => $sort,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function products(): array
    {
        $elements = fn (FiveElement ...$items): array => array_map(fn (FiveElement $item): string => $item->value, $items);

        return [
            [
                'category' => 'linh-vat', 'slug' => 'ty-huu-bac', 'sku' => 'TH-B925', 'glyph' => '貔',
                'name' => 'Tỳ hưu bạc S925 khai quang',
                'summary' => 'Chiêu tài, giữ lộc',
                'description' => 'Tỳ hưu đúc bạc nguyên khối, miệng rộng bụng to - tượng trưng cho việc thu tài mà không thất thoát. Được Thầy Minh Quang khai quang theo ngày giờ hợp tuổi người nhận.',
                'elements' => $elements(FiveElement::Kim, FiveElement::Thuy),
                'badge' => 'Bán chạy', 'price' => 1290000, 'compare_price' => 1590000, 'stock' => 14, 'sold_count' => 420,
                'rating' => 4.9, 'reviews_count' => 86,
                'sizes' => [
                    ['name' => 'Nhỏ · 3cm', 'price' => 1290000, 'compare_price' => 1590000],
                    ['name' => 'Vừa · 5cm', 'price' => 1890000, 'compare_price' => 2290000],
                    ['name' => 'Lớn · 8cm', 'price' => 2990000, 'compare_price' => 3590000],
                ],
                'styles' => ['Mặt dây chuyền', 'Tượng để bàn'],
                'allows_engraving' => true,
                'meaning' => "Tỳ hưu là linh thú thứ chín của Long vương, chỉ ăn vàng bạc châu báu mà không bài tiết – vì thế được xem là biểu tượng của chiêu tài, giữ lộc. Người làm kinh doanh, buôn bán thường thỉnh Tỳ hưu để thu hút khách và giữ tiền.\nBạc thuộc hành Kim, tương sinh với hành Thủy, rất hợp người mệnh Kim và Thủy. Bạc còn có tính hút khí xấu – khi bạc xỉn màu là dấu hiệu vật phẩm đang hấp thụ uế khí cho chủ nhân.",
                'usage' => [
                    'Đeo mặt dây hướng đầu Tỳ hưu ra ngoài, không quay vào thân người.',
                    'Tượng để bàn đặt hướng ra cửa chính hoặc cửa sổ, không đối diện gương hay giường ngủ.',
                    'Không để người khác sờ vào đầu, mắt, miệng Tỳ hưu.',
                    'Lau bằng khăn mềm, tránh hóa chất; bạc xỉn có thể đánh lại tại Tâm Minh miễn phí.',
                ],
                'specs' => [
                    ['label' => 'Chất liệu', 'value' => 'Bạc S925 nguyên khối'],
                    ['label' => 'Trọng lượng', 'value' => '8 – 42 gram tùy kích thước'],
                    ['label' => 'Xuất xứ', 'value' => 'Chế tác thủ công tại Hà Nội'],
                    ['label' => 'Đi kèm', 'value' => 'Hộp gỗ, giấy kiểm định, dây da'],
                ],
                'reviews' => [
                    ['rating' => 5, 'name' => 'Anh Hoàng Long', 'note' => 'Mệnh Kim', 'quote' => 'Bạc đúc sắc nét, có giấy kiểm định đầy đủ. Đeo được 2 tháng, việc buôn bán ổn định hơn hẳn.'],
                    ['rating' => 5, 'name' => 'Chị Mai Anh', 'note' => 'Mệnh Thủy', 'quote' => 'Được tư vấn kỹ qua Zalo trước khi mua, hộp quà đẹp, khắc tên rất tinh tế.'],
                ],
            ],
            ['category' => 'vong-tay', 'slug' => 'vong-thach-anh-trang', 'name' => 'Vòng thạch anh trắng', 'glyph' => '晶', 'summary' => 'Thanh lọc khí, tăng minh mẫn', 'elements' => $elements(FiveElement::Kim, FiveElement::Thuy), 'price' => 690000, 'sold_count' => 310],
            ['category' => 'linh-vat', 'slug' => 'thiem-thu-ngoc-bich', 'name' => 'Thiềm thừ ngọc bích', 'glyph' => '蟾', 'summary' => 'Đặt quầy thu ngân, hút tài', 'elements' => $elements(FiveElement::Moc, FiveElement::Hoa), 'price' => 1850000, 'sold_count' => 120],
            ['category' => 'da-phong-thuy', 'slug' => 'cau-thach-anh-tim', 'name' => 'Cầu thạch anh tím', 'glyph' => '紫', 'summary' => 'Bàn làm việc, tăng tập trung', 'elements' => $elements(FiveElement::Hoa, FiveElement::Tho), 'price' => 980000, 'sold_count' => 150],
            ['category' => 'vong-tay', 'slug' => 'vong-ma-nao-do', 'name' => 'Vòng mã não đỏ', 'glyph' => '瑪', 'summary' => 'Tăng dương khí, tự tin', 'elements' => $elements(FiveElement::Hoa, FiveElement::Tho), 'price' => 550000, 'sold_count' => 205],
            ['category' => 'tranh-thu-phap', 'slug' => 'tranh-chu-tam', 'name' => 'Tranh chữ Tâm', 'glyph' => '心', 'summary' => 'Thư pháp tay, khung gỗ hương', 'elements' => $elements(FiveElement::Moc, FiveElement::Hoa), 'badge' => 'Mới', 'price' => 1450000, 'sold_count' => 12],
            ['category' => 'cay-phong-thuy', 'slug' => 'kim-tien-thu', 'name' => 'Kim tiền thụ', 'glyph' => '錢', 'summary' => 'Cây để bàn, sinh tài lộc', 'elements' => $elements(FiveElement::Moc, FiveElement::Hoa), 'price' => 420000, 'sold_count' => 260],
            ['category' => 'linh-vat', 'slug' => 'long-quy-dong', 'name' => 'Long quy đồng', 'glyph' => '龜', 'summary' => 'Trấn trạch, hóa sát', 'elements' => $elements(FiveElement::Thuy, FiveElement::Moc), 'price' => 1690000, 'sold_count' => 95],
            ['category' => 'vong-tay', 'slug' => 'vong-hac-dieu-thach', 'name' => 'Vòng hắc diệu thạch', 'glyph' => '黑', 'summary' => 'Hộ thân, trừ tà khí', 'elements' => $elements(FiveElement::Thuy, FiveElement::Moc), 'price' => 620000, 'sold_count' => 180],
            ['category' => 'da-phong-thuy', 'slug' => 'cay-thach-anh-vang', 'name' => 'Cây thạch anh vàng', 'glyph' => '金', 'summary' => 'Cung tài lộc phía Đông Nam', 'elements' => $elements(FiveElement::Kim, FiveElement::Tho), 'price' => 1150000, 'sold_count' => 88],
            ['category' => 'tranh-thu-phap', 'slug' => 'tranh-ma-dao-thanh-cong', 'name' => 'Tranh Mã đáo thành công', 'glyph' => '馬', 'summary' => 'Sự nghiệp hanh thông', 'elements' => $elements(FiveElement::Hoa, FiveElement::Tho), 'price' => 2200000, 'sold_count' => 64],
            ['category' => 'cay-phong-thuy', 'slug' => 'luoi-ho-de-ban', 'name' => 'Lưỡi hổ để bàn', 'glyph' => '虎', 'summary' => 'Hút khí xấu, giữ bình an', 'elements' => $elements(FiveElement::Kim, FiveElement::Moc), 'price' => 280000, 'sold_count' => 330],
        ];
    }
}
