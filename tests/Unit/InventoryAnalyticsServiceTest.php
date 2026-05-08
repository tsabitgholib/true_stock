<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Item;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Company;
use App\Models\Division;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Domain\Inventory\Services\InventoryAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryAnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsService = new InventoryAnalyticsService();

        // Create user for AuditLog
        \App\Models\User::create([
            'id' => 1,
            'name' => 'System',
            'email' => 'system@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password')
        ]);
    }

    public function test_can_calculate_inventory_valuation()
    {
        // 1. Setup Master Data
        $company = Company::create(['name' => 'Test', 'code' => 'TST']);
        $division = Division::create(['company_id' => $company->id, 'name' => 'Test', 'code' => 'TST']);
        $warehouse = Warehouse::create(['division_id' => $division->id, 'name' => 'Main Warehouse', 'code' => 'MWH']);
        $category = ItemCategory::create(['name' => 'Test', 'code' => 'TST']);
        $unit = Unit::create(['name' => 'Test', 'code' => 'TST']);

        $itemA = Item::create([
            'item_code' => 'A',
            'item_name' => 'Item A',
            'standard_cost' => 100,
            'item_category_id' => $category->id,
            'unit_id' => $unit->id
        ]);

        $itemB = Item::create([
            'item_code' => 'B',
            'item_name' => 'Item B',
            'standard_cost' => 250,
            'item_category_id' => $category->id,
            'unit_id' => $unit->id
        ]);

        // 2. Setup Stocks
        Stock::create(['item_id' => $itemA->id, 'warehouse_id' => $warehouse->id, 'quantity' => 10]); // Value: 1000
        Stock::create(['item_id' => $itemB->id, 'warehouse_id' => $warehouse->id, 'quantity' => 4]);  // Value: 1000

        // 3. Test KPIs
        $kpis = $this->analyticsService->getDashboardKPIs();
        $this->assertEquals(2000, $kpis['total_valuation']);
        $this->assertEquals(2, $kpis['total_items']);

        // 4. Test Warehouse Valuation
        $valuation = $this->analyticsService->getValuationByWarehouse();
        $this->assertCount(1, $valuation);
        $this->assertEquals('Main Warehouse', $valuation[0]->name);
        $this->assertEquals(2000, $valuation[0]->valuation);
    }
}
