<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StockBatch extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'received_date',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'received_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Sisa hari sampai kedaluwarsa. Negatif = sudah lewat. */
    public function daysLeft(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }
}
