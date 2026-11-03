<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Giỏ hàng lưu trong session (không cần đăng nhập). Chỉ lưu mã vật phẩm, lựa chọn và số lượng;
 * giá luôn tính lại từ cơ sở dữ liệu khi hiển thị.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    private const MAX_QUANTITY = 99;

    /**
     * Thêm vào giỏ; cùng vật phẩm và cùng lựa chọn thì cộng dồn số lượng.
     */
    public function add(Product $product, int $quantity, ?string $size, ?string $style, ?string $engraving): void
    {
        $key = $this->keyFor($product->getKey(), $size, $style, $engraving);
        $items = $this->items();
        $quantity += $items[$key]['quantity'] ?? 0;

        $items[$key] = [
            'product_id' => $product->getKey(),
            'size' => $size,
            'style' => $style,
            'engraving' => $engraving,
            'quantity' => min($quantity, self::MAX_QUANTITY),
        ];

        session()->put(self::SESSION_KEY, $items);
    }

    public function setQuantity(string $key, int $quantity): void
    {
        $items = $this->items();

        if (! isset($items[$key])) {
            return;
        }

        if ($quantity < 1) {
            unset($items[$key]);
        } else {
            $items[$key]['quantity'] = min($quantity, self::MAX_QUANTITY);
        }

        session()->put(self::SESSION_KEY, $items);
    }

    public function remove(string $key): void
    {
        $this->setQuantity($key, 0);
    }

    /**
     * Xoá cả giỏ, sau khi đặt hàng thành công.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Số sản phẩm trong giỏ (cộng số lượng các dòng), chỉ đọc session nên rẻ để gọi ở header mọi trang.
     */
    public function count(): int
    {
        return (int) collect($this->items())->sum('quantity');
    }

    /**
     * Các dòng còn bán được, số lượng bị giới hạn theo tồn kho hiện tại.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        $products = Product::query()->active()->whereKey(collect($items)->pluck('product_id'))->get()->keyBy('id');

        return collect($items)
            ->map(function (array $item, string $key) use ($products): ?CartLine {
                $product = $products->get($item['product_id']);

                if ($product === null || $product->stock < 1) {
                    return null;
                }

                $price = collect($product->priceOptions())->firstWhere('name', (string) $item['size']) ?? $product->priceOptions()[0];

                return new CartLine(
                    key: $key,
                    product: $product,
                    size: $item['size'],
                    style: $item['style'],
                    engraving: $item['engraving'],
                    quantity: min($item['quantity'], $product->stock),
                    unitPrice: $price['price'],
                    comparePrice: $price['compare_price'],
                );
            })
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public function total(Collection $lines): int
    {
        return (int) $lines->sum(fn (CartLine $line): int => $line->subtotal());
    }

    /**
     * @return array<string, array{product_id: int, size: ?string, style: ?string, engraving: ?string, quantity: int}>
     */
    private function items(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    private function keyFor(int $productId, ?string $size, ?string $style, ?string $engraving): string
    {
        return md5(implode('|', [$productId, $size, $style, mb_strtolower((string) $engraving)]));
    }
}
