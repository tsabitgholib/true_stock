<?php

namespace App\Domain\Inventory\Services;

use App\Application\Inventory\DTOs\StockMovementData;
use App\Domain\Inventory\Events\StockMovementOccurred;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Increase stock
     */
    public function stockIn(StockMovementData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::where([
                'item_id' => $data->item_id,
                'warehouse_id' => $data->warehouse_id,
                'location_id' => $data->location_id,
                'rack_id' => $data->rack_id,
                'batch_id' => $data->batch_id,
            ])->lockForUpdate()->first();

            if (!$stock) {
                $stock = Stock::create([
                    'item_id' => $data->item_id,
                    'warehouse_id' => $data->warehouse_id,
                    'location_id' => $data->location_id,
                    'rack_id' => $data->rack_id,
                    'batch_id' => $data->batch_id,
                    'quantity' => 0,
                ]);
            }

            $stock->increment('quantity', $data->quantity);

            StockMovementOccurred::dispatch($data);

            return $stock;
        });
    }

    /**
     * Get available stock (Physical - Reserved)
     */
    public function getAvailableStock(int $itemId, int $warehouseId, ?int $locationId = null, ?int $rackId = null, ?int $batchId = null): float
    {
        $physicalStock = Stock::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'location_id' => $locationId,
            'rack_id' => $rackId,
            'batch_id' => $batchId,
        ])->value('quantity') ?? 0;

        $reservedStock = \App\Models\StockReservation::active()
            ->where([
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'rack_id' => $rackId,
                'batch_id' => $batchId,
            ])->sum('quantity');

        return max(0, $physicalStock - $reservedStock);
    }

    /**
     * Decrease stock (Updated to respect reservations)
     */
    public function stockOut(StockMovementData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::where([
                'item_id' => $data->item_id,
                'warehouse_id' => $data->warehouse_id,
                'location_id' => $data->location_id,
                'rack_id' => $data->rack_id,
                'batch_id' => $data->batch_id,
            ])->lockForUpdate()->first();

            if (!$stock) {
                throw new \Exception("Stock record not found for item {$data->item_id} at warehouse {$data->warehouse_id}");
            }

            // Calculate available stock
            $available = $this->getAvailableStock(
                $data->item_id, 
                $data->warehouse_id, 
                $data->location_id, 
                $data->rack_id, 
                $data->batch_id
            );

            // Special case: if we are fulfilling a reservation, we don't count its own reserved quantity against us.
            // But for simplicity in this MVP, we check if physical stock is enough.
            // In enterprise, we'd pass a reservation_id to 'release and deduct' in one go.
            
            if ($stock->quantity < $data->quantity) {
                throw new \Exception("Insufficient physical stock for item {$data->item_id}");
            }

            if ($available < $data->quantity && $data->reference_type !== 'StockReservationFulfillment') {
                 throw new \Exception("Insufficient available stock (Reserved) for item {$data->item_id}. Available: {$available}");
            }

            $stock->decrement('quantity', $data->quantity);

            StockMovementOccurred::dispatch($data);

            return $stock;
        });
    }

    /**
     * Create a stock reservation
     */
    public function reserveStock(array $rawData): \App\Models\StockReservation
    {
        return DB::transaction(function () use ($rawData) {
            $available = $this->getAvailableStock(
                $rawData['item_id'],
                $rawData['warehouse_id'],
                $rawData['location_id'] ?? null,
                $rawData['rack_id'] ?? null,
                $rawData['batch_id'] ?? null
            );

            if ($available < $rawData['quantity']) {
                throw new \Exception("Insufficient stock to reserve. Available: {$available}");
            }

            return \App\Models\StockReservation::create([
                'reservation_number' => 'RES-' . strtoupper(uniqid()),
                'item_id' => $rawData['item_id'],
                'warehouse_id' => $rawData['warehouse_id'],
                'location_id' => $rawData['location_id'] ?? null,
                'rack_id' => $rawData['rack_id'] ?? null,
                'batch_id' => $rawData['batch_id'] ?? null,
                'quantity' => $rawData['quantity'],
                'status' => 'ACTIVE',
                'reference_type' => $rawData['reference_type'] ?? null,
                'reference_id' => $rawData['reference_id'] ?? null,
                'expires_at' => $rawData['expires_at'] ?? now()->addDays(3),
                'user_id' => Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Cancel/Release reservation
     */
    public function releaseReservation(int $reservationId, string $status = 'CANCELLED'): void
    {
        $reservation = \App\Models\StockReservation::findOrFail($reservationId);
        $reservation->update(['status' => $status]);
    }

    /**
     * Initiate stock transfer (Moves stock out and sets to IN_TRANSIT)
     */
    public function initiateTransfer(int $transferId): void
    {
        DB::transaction(function () use ($transferId) {
            $transfer = \App\Models\StockTransfer::with('details')->findOrFail($transferId);

            if ($transfer->status !== 'PENDING') {
                throw new \Exception("Transfer is not in PENDING status.");
            }

            foreach ($transfer->details as $detail) {
                // Deduct from source warehouse
                $this->stockOut(new StockMovementData(
                    item_id: $detail->item_id,
                    warehouse_id: $transfer->from_warehouse_id,
                    quantity: $detail->quantity,
                    movement_type: 'TRANSFER_OUT',
                    location_id: $detail->from_location_id,
                    rack_id: $detail->from_rack_id,
                    batch_id: $detail->batch_id,
                    reference_type: 'StockTransfer',
                    reference_id: $transfer->id,
                    remarks: "Transfer #{$transfer->transfer_number} to WH {$transfer->to_warehouse_id}",
                ));
            }

            $transfer->update([
                'status' => 'IN_TRANSIT',
                'shipped_at' => now(),
            ]);
        });
    }

    /**
     * Complete stock transfer (Receives stock at destination)
     */
    public function completeTransfer(int $transferId): void
    {
        DB::transaction(function () use ($transferId) {
            $transfer = \App\Models\StockTransfer::with('details')->findOrFail($transferId);

            if ($transfer->status !== 'IN_TRANSIT') {
                throw new \Exception("Transfer is not in IN_TRANSIT status.");
            }

            foreach ($transfer->details as $detail) {
                // Add to destination warehouse
                $this->stockIn(new StockMovementData(
                    item_id: $detail->item_id,
                    warehouse_id: $transfer->to_warehouse_id,
                    quantity: $detail->quantity,
                    movement_type: 'TRANSFER_IN',
                    location_id: $detail->to_location_id,
                    rack_id: $detail->to_rack_id,
                    batch_id: $detail->batch_id,
                    reference_type: 'StockTransfer',
                    reference_id: $transfer->id,
                    remarks: "Received Transfer #{$transfer->transfer_number} from WH {$transfer->from_warehouse_id}",
                ));
            }

            $transfer->update([
                'status' => 'COMPLETED',
                'received_at' => now(),
                'confirmed_by' => Auth::id() ?? 1,
            ]);
        });
    }

    /**
     * Transfer stock (Deprecated: use initiate/complete for in-transit)
     */
    public function stockTransfer(array $rawData): void
    {
        // For backward compatibility or simple direct transfers
        DB::transaction(function () use ($rawData) {
            // Out from source
            $this->stockOut(new StockMovementData(
                item_id: $rawData['item_id'],
                warehouse_id: $rawData['from_warehouse_id'],
                quantity: $rawData['quantity'],
                movement_type: 'TRANSFER_OUT',
                location_id: $rawData['from_location_id'] ?? null,
                rack_id: $rawData['from_rack_id'] ?? null,
                batch_id: $rawData['batch_id'] ?? null,
                reference_type: 'Transfer',
                reference_id: $rawData['reference_id'] ?? null,
                remarks: "Transfer to WH {$rawData['to_warehouse_id']}",
            ));

            // In to destination
            $this->stockIn(new StockMovementData(
                item_id: $rawData['item_id'],
                warehouse_id: $rawData['to_warehouse_id'],
                quantity: $rawData['quantity'],
                movement_type: 'TRANSFER_IN',
                location_id: $rawData['to_location_id'] ?? null,
                rack_id: $rawData['to_rack_id'] ?? null,
                batch_id: $rawData['batch_id'] ?? null,
                reference_type: 'Transfer',
                reference_id: $rawData['reference_id'] ?? null,
                remarks: "Transfer from WH {$rawData['from_warehouse_id']}",
            ));
        });
    }

    /**
     * Pick stock based on strategy (FIFO, LIFO)
     */
    public function pickStock(int $itemId, int $warehouseId, float $requestedQuantity, string $strategy = 'FIFO'): array
    {
        $stocks = Stock::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with('batch')
            ->join('stock_batches', 'stocks.batch_id', '=', 'stock_batches.id')
            ->orderBy('stock_batches.expiry_date', $strategy === 'FIFO' ? 'asc' : 'desc')
            ->select('stocks.*')
            ->get();

        $picked = [];
        $remaining = $requestedQuantity;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) break;

            $take = min($stock->quantity, $remaining);
            $picked[] = [
                'stock_id' => $stock->id,
                'batch_id' => $stock->batch_id,
                'quantity' => $take
            ];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception("Not enough stock available for item {$itemId} using {$strategy} strategy. Shortfall: {$remaining}");
        }

        return $picked;
    }
}
