<?php

namespace App\Domain\Inventory\Listeners;

use App\Domain\Inventory\Events\StockMovementOccurred;
use App\Models\StockMovement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class RecordStockMovement
{
    /**
     * Handle the event.
     */
    public function handle(StockMovementOccurred $event): void
    {
        $data = $event->data;

        StockMovement::create([
            'item_id' => $data->item_id,
            'warehouse_id' => $data->warehouse_id,
            'location_id' => $data->location_id,
            'rack_id' => $data->rack_id,
            'batch_id' => $data->batch_id,
            'movement_type' => $data->movement_type,
            'quantity' => $data->quantity,
            'reference_type' => $data->reference_type,
            'reference_id' => $data->reference_id,
            'user_id' => $data->user_id ?? Auth::id() ?? 1,
            'remarks' => $data->remarks,
        ]);
    }
}
