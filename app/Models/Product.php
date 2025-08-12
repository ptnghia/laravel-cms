<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'content',
        'images',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'stock_status',
        'attributes',
        'variations',
        'category_id',
        'rating_avg',
        'rating_count',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'attributes' => 'array',
            'variations' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'rating_avg' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the ratings for the product.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock')
                    ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include on-sale products.
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
                    ->where('sale_price', '>', 0);
    }

    /**
     * Scope a query to order by price.
     */
    public function scopeOrderByPrice($query, string $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Scope a query to order by rating.
     */
    public function scopeOrderByRating($query)
    {
        return $query->orderBy('rating_avg', 'desc');
    }

    /**
     * Check if the product is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_status === 'in_stock' && $this->stock_quantity > 0;
    }

    /**
     * Check if the product is on sale.
     */
    public function isOnSale(): bool
    {
        return $this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    /**
     * Get the effective price (sale price if on sale, otherwise regular price).
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->isOnSale()) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Get the main product image.
     */
    public function getMainImageAttribute(): ?string
    {
        if (empty($this->images)) {
            return null;
        }

        $firstImage = $this->images[0];
        return is_string($firstImage) ? asset('storage/' . $firstImage) : null;
    }

    /**
     * Get all product image URLs.
     */
    public function getImageUrlsAttribute(): array
    {
        if (empty($this->images)) {
            return [];
        }

        return array_map(function ($image) {
            return is_string($image) ? asset('storage/' . $image) : $image;
        }, $this->images);
    }

    /**
     * Decrease stock quantity.
     */
    public function decreaseStock(int $quantity): void
    {
        $newQuantity = max(0, $this->stock_quantity - $quantity);

        $this->update([
            'stock_quantity' => $newQuantity,
            'stock_status' => $newQuantity > 0 ? 'in_stock' : 'out_of_stock',
        ]);
    }

    /**
     * Increase stock quantity.
     */
    public function increaseStock(int $quantity): void
    {
        $newQuantity = $this->stock_quantity + $quantity;

        $this->update([
            'stock_quantity' => $newQuantity,
            'stock_status' => 'in_stock',
        ]);
    }

    /**
     * Update rating statistics.
     */
    public function updateRatingStats(): void
    {
        $ratings = $this->ratings();

        $this->update([
            'rating_avg' => $ratings->avg('rating') ?: 0,
            'rating_count' => $ratings->count(),
        ]);
    }
}
