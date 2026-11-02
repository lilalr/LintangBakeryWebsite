<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'buyer',
        'phone',
        'address',
        'city',
        'province',
        'total',
        'dp',
        'remaining',
        'payment_type',
        'payment_method',
        'status',
        'payment_proof',
        'final_payment_proof',
        'final_payment_method',
        'settlement_proof',
        'settlement_status',
        'shipping_cost',
        'courier_name',
        'tracking_number',
        'shipping_notes',
        'remaining_paid_at',
        'stock_deducted',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'dp' => 'integer',
            'remaining' => 'integer',
            'shipping_cost' => 'integer',
            'stock_deducted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
