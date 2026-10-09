<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineTransactionItem extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'quantity',
        'unit_price',
        'subtotal',
        'harga_modal_at_purchase',
        'harga_jual_at_purchase',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'harga_modal_at_purchase' => 'decimal:2',
            'harga_jual_at_purchase' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
