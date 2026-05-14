<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseRack extends Model
{
    use SoftDeletes;
    protected $fillable = ['location_id', 'name', 'code'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }
}
