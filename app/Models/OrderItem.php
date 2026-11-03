<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một dòng vật phẩm trong đơn, chốt tên/lựa chọn/giá tại lúc đặt; `product_id` về null khi vật phẩm bị xoá.
 */
#[Fillable(['product_id', 'product_name', 'size', 'style', 'engraving', 'unit_price', 'quantity', 'subtotal'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Các lựa chọn đã chọn, ví dụ "Vừa · Mặt dây chuyền · Khắc: Minh".
     */
    public function optionsLabel(): string
    {
        return collect([$this->size, $this->style, $this->engraving ? 'Khắc: '.$this->engraving : null])->filter()->implode(' · ');
    }
}
