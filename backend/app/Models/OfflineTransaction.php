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
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfflineTransactionItem::class);
    }
}
