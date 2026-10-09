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
    ];

    protected function casts(): array
    {
        return [
            'price_per_bal' => 'integer',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
