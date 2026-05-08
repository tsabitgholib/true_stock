<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Infrastructure\Traits\Loggable;

class Item extends Model
{
    use Loggable;

    protected $fillable = [
        'item_code', 'item_name', 'description', 'item_category_id',
        'unit_id', 'item_type', 'weight', 'dimension',
        'barcode', 'qr_code', 'reorder_level', 'safety_stock', 'max_stock'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
