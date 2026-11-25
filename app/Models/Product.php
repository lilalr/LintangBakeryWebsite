<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_per_bal',
        'cost_price',
        'image',
        'category',
        'sales_channel',
        'stock',
        'min_stock',
    ];

    protected function casts(): array
    {
        return [
            'price_per_bal' => 'integer',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /** Status stok: Aman / Menipis / Habis */
    public function stockStatus(): string
    {
        if ($this->stock <= 0) {
            return 'Habis';
        }

        if ($this->stock <= $this->min_stock) {
            return 'Menipis';
        }

        return 'Aman';
    }
}
