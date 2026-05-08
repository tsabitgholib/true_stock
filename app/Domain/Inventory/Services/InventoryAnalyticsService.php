<?php

namespace App\Domain\Inventory\Services;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryAnalyticsService
{
    /**
     * Get summary KPIs for dashboard
     */
    public function getDashboardKPIs(): array
    {
        $totalItems = Item::count();
        $totalValuation = DB::table('stocks')
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->sum(DB::raw('stocks.quantity * items.standard_cost'));

        $lowStockCount = Item::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('stocks')
                ->whereColumn('stocks.item_id', 'items.id')
                ->groupBy('item_id')
                ->having(DB::raw('SUM(quantity)'), '<=', DB::raw('items.reorder_level'));
        })->count();

        $recentMovements = StockMovement::with(['item', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_items' => $totalItems,
            'total_valuation' => $totalValuation,
            'low_stock_count' => $lowStockCount,
            'recent_movements' => $recentMovements,
        ];
    }

    /**
     * Get stock aging (simplified)
     */
    public function getStockAging(): array
    {
        // Items not moved for > 30, 60, 90 days
        $agingData = [
            'fast_moving' => 0,
            'slow_moving' => 0,
            'dead_stock' => 0,
        ];

        // Logic would involve comparing last movement date per item/batch
        return $agingData;
    }

    /**
     * Get valuation by warehouse
     */
    public function getValuationByWarehouse(): array
    {
        return DB::table('stocks')
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->select('warehouses.name', DB::raw('SUM(stocks.quantity * items.standard_cost) as valuation'))
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get()
            ->toArray();
    }
}
