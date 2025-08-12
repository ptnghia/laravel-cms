<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'product_options',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'product_options' => 'array',
        ];
    }

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the total price for this item.
     */
    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Update the total price based on quantity and unit price.
     */
    public function updateTotal(): void
    {
        $this->update(['total_price' => $this->quantity * $this->unit_price]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderItem) {
            $orderItem->total_price = $orderItem->quantity * $orderItem->unit_price;
        });

        static::updating(function ($orderItem) {
            if ($orderItem->isDirty(['quantity', 'unit_price'])) {
                $orderItem->total_price = $orderItem->quantity * $orderItem->unit_price;
            }
        });

        static::saved(function ($orderItem) {
            // Recalculate order totals when order item is saved
            $orderItem->order->calculateTotals();
        });
    }
}
