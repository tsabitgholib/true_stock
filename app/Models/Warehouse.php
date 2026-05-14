<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Infrastructure\Traits\Loggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use Loggable, SoftDeletes;
    protected $fillable = ['division_id', 'name', 'code', 'address'];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}
