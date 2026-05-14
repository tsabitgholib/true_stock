<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UomConversion extends Model
{
    protected $fillable = ['item_id', 'from_unit_id', 'to_unit_id', 'factor'];

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Convert quantity from one unit to another
     */
    public static function convert(float $quantity, int $fromUnitId, int $toUnitId, ?int $itemId = null): float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        // Try item-specific conversion first
        $conversion = self::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->where('item_id', $itemId)
            ->first();

        // Fallback to global conversion
        if (!$conversion && $itemId !== null) {
            $conversion = self::where('from_unit_id', $fromUnitId)
                ->where('to_unit_id', $toUnitId)
                ->whereNull('item_id')
                ->first();
        }

        if ($conversion) {
            return $quantity * $conversion->factor;
        }

        // Try inverse conversion
        $inverse = self::where('from_unit_id', $toUnitId)
            ->where('to_unit_id', $fromUnitId)
            ->where('item_id', $itemId)
            ->first();

        if (!$inverse && $itemId !== null) {
            $inverse = self::where('from_unit_id', $toUnitId)
                ->where('to_unit_id', $fromUnitId)
                ->whereNull('item_id')
                ->first();
        }

        if ($inverse) {
            return $quantity / $inverse->factor;
        }

        throw new \Exception("UOM Conversion not found from Unit ID {$fromUnitId} to Unit ID {$toUnitId}");
    }
}
