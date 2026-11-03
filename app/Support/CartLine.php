<?php

namespace App\Support;

use App\Models\Product;

/**
 * Một dòng trong giỏ hàng đã tính giá theo dữ liệu hiện tại của vật phẩm.
 */
final readonly class CartLine
{
    public function __construct(
        public string $key,
        public Product $product,
        public ?string $size,
        public ?string $style,
        public ?string $engraving,
        public int $quantity,
        public int $unitPrice,
        public ?int $comparePrice,
    ) {}

    public function subtotal(): int
    {
        return $this->unitPrice * $this->quantity;
    }

    /**
     * Các lựa chọn đã chọn, ví dụ "Vừa · 5cm · Mặt dây chuyền · Khắc: Minh".
     */
    public function optionsLabel(): string
    {
        return collect([$this->size, $this->style, $this->engraving ? 'Khắc: '.$this->engraving : null])->filter()->implode(' · ');
    }
}
