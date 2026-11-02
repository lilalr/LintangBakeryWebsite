<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'admin_name',
        'type',
        'quantity',
        'balance',
        'reason',
        'source',
        'expiry_date',
        'reference_number',
        'loss_amount',
        'notes',
        'mutation_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'balance' => 'integer',
            'loss_amount' => 'decimal:2',
            'expiry_date' => 'date',
            'mutation_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
