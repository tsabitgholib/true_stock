<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Division;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Stock;
use App\Domain\Inventory\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;
    private $item;
    private $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();

        // Setup user
        User::create([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);

        // Setup basic data
        $company = Company::create(['name' => 'Test', 'code' => 'TST']);
        $division = Division::create(['company_id' => $company->id, 'name' => 'Test', 'code' => 'TST']);
        $this->warehouse = Warehouse::create(['division_id' => $division->id, 'name' => 'Test', 'code' => 'TST']);
        
        $category = ItemCategory::create(['name' => 'Test', 'code' => 'TST']);
        $unit = Unit::create(['name' => 'Test', 'code' => 'TST']);
        $this->item = Item::create([
            'item_code' => 'ITEM01',
            'item_name' => 'Test Item',
            'item_category_id' => $category->id,
            'unit_id' => $unit->id
        ]);
    }

    public function test_can_increase_stock()
    {
        $this->stockService->stockIn([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $stock = Stock::where('item_id', $this->item->id)->first();
        $this->assertEquals(100, $stock->quantity);
    }

    public function test_can_decrease_stock()
    {
        // Initial stock
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $this->stockService->stockOut([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 40,
        ]);

        $stock = Stock::where('item_id', $this->item->id)->first();
        $this->assertEquals(60, $stock->quantity);
    }

    public function test_throws_exception_on_insufficient_stock()
    {
        $this->expectException(\Exception::class);

        $this->stockService->stockOut([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);
    }
}
