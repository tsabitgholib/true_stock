<?php

namespace App\Interfaces\Http\Controllers;

use App\Domain\Inventory\Services\InventoryAnalyticsService;
use App\Models\Warehouse;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    private InventoryAnalyticsService $analyticsService;

    public function __construct(InventoryAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['warehouse_id', 'item_id']);
        
        return Inertia::render('Inventory/Index', [
            'stocks' => $this->analyticsService->getStockBalances($filters),
            'warehouses' => Warehouse::all(),
            'items' => Item::all(['id', 'item_name', 'item_code']),
            'filters' => $filters
        ]);
    }
}
