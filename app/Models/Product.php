<?php

namespace App\Models;

use App\FiveElement;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Vật phẩm phong thủy, truy cập qua `slug`. `price` là giá bán mặc định (và là khóa sắp xếp);
 * khi có `sizes` thì giá hiển thị theo kích thước đang chọn.
 * `sizes` là [{name, price, compare_price}], `styles` là danh sách tên kiểu dáng, `usage` là danh sách ý,
 * `specs` là [{label, value}], `reviews` là [{rating, quote, name, note}], `images` là đường dẫn trên disk `public`.
 */
#[Fillable([
    'product_category_id', 'name', 'slug', 'sku', 'glyph', 'summary', 'description', 'elements', 'badge',
    'price', 'compare_price', 'stock', 'sold_count', 'rating', 'reviews_count',
    'images', 'sizes', 'styles', 'allows_engraving', 'meaning', 'usage', 'specs', 'reviews', 'is_active', 'sort',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'elements' => AsEnumCollection::of(FiveElement::class),
            'images' => 'array',
            'sizes' => 'array',
            'styles' => 'array',
            'usage' => 'array',
            'specs' => 'array',
            'reviews' => 'array',
            'rating' => 'decimal:1',
            'allows_engraving' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Chỉ lấy vật phẩm hợp với bản mệnh đã chọn.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forElement(Builder $query, FiveElement $element): void
    {
        $query->whereJsonContains('elements', $element->value);
    }

    /**
     * Đường dẫn công khai của các ảnh đã tải lên.
     *
     * @return list<string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path): string => Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function coverUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    /**
     * Dữ liệu kích thước cho trang chi tiết: nếu chưa khai báo thì dùng một mức giá duy nhất.
     *
     * @return list<array{name: string, price: int, compare_price: ?int}>
     */
    public function priceOptions(): array
    {
        $sizes = collect($this->sizes ?? [])
            ->map(fn (array $size): array => [
                'name' => (string) $size['name'],
                'price' => (int) $size['price'],
                'compare_price' => filled($size['compare_price'] ?? null) ? (int) $size['compare_price'] : null,
            ])
            ->values()
            ->all();

        return $sizes ?: [['name' => '', 'price' => $this->price, 'compare_price' => $this->compare_price]];
    }

    /**
     * Tên các bản mệnh hợp, ví dụ "Kim · Thủy".
     */
    public function elementLabels(string $separator = ' · '): string
    {
        return $this->elements->map(fn (FiveElement $element): string => $element->getLabel())->implode($separator);
    }
}
