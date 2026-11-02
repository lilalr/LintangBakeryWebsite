<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfflineTransaction extends Model
{
    protected $fillable = [
        'date',
        'buyer_name',
        'total',
        'total_hpp',
        'total_laba',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total' => 'integer',
            'total_hpp' => 'decimal:2',
            'total_laba' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfflineTransactionItem::class);
    }
}
