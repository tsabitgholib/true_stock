<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Infrastructure\Traits\Loggable;

class GoodsReceipt extends Model
{
    use Loggable;

    protected $fillable = ['grn_number', 'vendor_id', 'warehouse_id', 'receive_date', 'status', 'remarks', 'user_id'];

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
