<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = ['item_id', 'batch_number', 'expiry_date', 'manufacturing_date'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
