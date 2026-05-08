<?php

namespace App\Domain\Inventory\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Increase stock
     */
    public function stockIn(array $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::where([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'rack_id' => $data['rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
            ])->lockForUpdate()->first();

            if (!$stock) {
                $stock = Stock::create([
                    'item_id' => $data['item_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'location_id' => $data['location_id'] ?? null,
                    'rack_id' => $data['rack_id'] ?? null,
                    'batch_id' => $data['batch_id'] ?? null,
                    'quantity' => 0,
                ]);
            }

            $stock->increment('quantity', $data['quantity']);

            StockMovement::create([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'rack_id' => $data['rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'movement_type' => 'STOCK_IN',
                'quantity' => $data['quantity'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'user_id' => Auth::id() ?? 1, // Fallback for testing/seeding
                'remarks' => $data['remarks'] ?? null,
            ]);

            return $stock;
        });
    }

    /**
     * Decrease stock
     */
    public function stockOut(array $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::where([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'rack_id' => $data['rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
            ])->lockForUpdate()->first();

            if (!$stock || $stock->quantity < $data['quantity']) {
                throw new \Exception("Insufficient stock for item {$data['item_id']} at warehouse {$data['warehouse_id']}");
            }

            $stock->decrement('quantity', $data['quantity']);

            StockMovement::create([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'rack_id' => $data['rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'movement_type' => 'STOCK_OUT',
                'quantity' => -$data['quantity'], // Negative for out
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'user_id' => Auth::id() ?? 1,
                'remarks' => $data['remarks'] ?? null,
            ]);

            return $stock;
        });
    }

    /**
     * Transfer stock
     */
    public function stockTransfer(array $data): void
    {
        DB::transaction(function () use ($data) {
            // Out from source
            $this->stockOut([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'location_id' => $data['from_location_id'] ?? null,
                'rack_id' => $data['from_rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'quantity' => $data['quantity'],
                'movement_type' => 'TRANSFER',
                'reference_type' => 'Transfer',
                'reference_id' => $data['reference_id'] ?? null,
                'remarks' => "Transfer to WH {$data['to_warehouse_id']}",
            ]);

            // In to destination
            $this->stockIn([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'location_id' => $data['to_location_id'] ?? null,
                'rack_id' => $data['to_rack_id'] ?? null,
                'batch_id' => $data['batch_id'] ?? null,
                'quantity' => $data['quantity'],
                'movement_type' => 'TRANSFER',
                'reference_type' => 'Transfer',
                'reference_id' => $data['reference_id'] ?? null,
                'remarks' => "Transfer from WH {$data['from_warehouse_id']}",
            ]);
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
