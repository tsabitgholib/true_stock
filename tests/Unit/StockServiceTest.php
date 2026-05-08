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
        $this->stockService->stockIn(new \App\Application\Inventory\DTOs\StockMovementData(
            item_id: $this->item->id,
            warehouse_id: $this->warehouse->id,
            quantity: 100,
            movement_type: 'STOCK_IN',
            remarks: 'Initial stock'
        ));

        $stock = Stock::where('item_id', $this->item->id)->first();
        $this->assertEquals(100, $stock->quantity);

        // Verify StockMovement recorded by listener
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'movement_type' => 'STOCK_IN',
            'remarks' => 'Initial stock'
        ]);
    }

    public function test_can_decrease_stock()
    {
        // Initial stock
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $this->stockService->stockOut(new \App\Application\Inventory\DTOs\StockMovementData(
            item_id: $this->item->id,
            warehouse_id: $this->warehouse->id,
            quantity: 40,
            movement_type: 'STOCK_OUT'
        ));

        $stock = Stock::where('item_id', $this->item->id)->first();
        $this->assertEquals(60, $stock->quantity);
    }

    public function test_throws_exception_on_insufficient_stock()
    {
        $this->expectException(\Exception::class);

        $this->stockService->stockOut(new \App\Application\Inventory\DTOs\StockMovementData(
            item_id: $this->item->id,
            warehouse_id: $this->warehouse->id,
            quantity: 10,
            movement_type: 'STOCK_OUT'
        ));
    }

    public function test_can_perform_in_transit_transfer()
    {
        // 1. Initial stock in WH A
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id, // WH A
            'quantity' => 100,
        ]);

        // Create WH B
        $warehouseB = Warehouse::create([
            'division_id' => $this->warehouse->division_id, 
            'name' => 'Warehouse B', 
            'code' => 'WHB'
        ]);

        // 2. Create Transfer Order
        $transfer = \App\Models\StockTransfer::create([
            'transfer_number' => 'TRF-001',
            'from_warehouse_id' => $this->warehouse->id,
            'to_warehouse_id' => $warehouseB->id,
            'status' => 'PENDING',
            'user_id' => 1
        ]);

        $transfer->details()->create([
            'item_id' => $this->item->id,
            'quantity' => 40
        ]);

        // 3. Initiate Transfer (Stock Out from A, status -> IN_TRANSIT)
        $this->stockService->initiateTransfer($transfer->id);

        $stockA = Stock::where('item_id', $this->item->id)->where('warehouse_id', $this->warehouse->id)->first();
        $this->assertEquals(60, $stockA->quantity);
        $this->assertEquals('IN_TRANSIT', $transfer->fresh()->status);

        // 4. Complete Transfer (Stock In to B, status -> COMPLETED)
        $this->stockService->completeTransfer($transfer->id);

        $stockB = Stock::where('item_id', $this->item->id)->where('warehouse_id', $warehouseB->id)->first();
        $this->assertEquals(40, $stockB->quantity);
        $this->assertEquals('COMPLETED', $transfer->fresh()->status);
    }

    public function test_can_reserve_stock_and_respects_it()
    {
        // 1. Setup physical stock: 100
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        // 2. Reserve 30 items
        $reservation = $this->stockService->reserveStock([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 30,
            'reference_type' => 'SalesOrder',
            'reference_id' => 'SO-001'
        ]);

        $this->assertEquals('ACTIVE', $reservation->status);
        
        // 3. Check available stock (Should be 100 - 30 = 70)
        $available = $this->stockService->getAvailableStock($this->item->id, $this->warehouse->id);
        $this->assertEquals(70, $available);

        // 4. Try to stockOut 80 items (Should FAIL because only 70 available, even if 100 physical)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Insufficient available stock (Reserved)");

        $this->stockService->stockOut(new \App\Application\Inventory\DTOs\StockMovementData(
            item_id: $this->item->id,
            warehouse_id: $this->warehouse->id,
            quantity: 80,
            movement_type: 'STOCK_OUT'
        ));
    }

    public function test_can_release_reservation_to_restore_availability()
    {
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $reservation = $this->stockService->reserveStock([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 30,
        ]);

        $this->assertEquals(70, $this->stockService->getAvailableStock($this->item->id, $this->warehouse->id));

        // Release it
        $this->stockService->releaseReservation($reservation->id);

        $this->assertEquals(100, $this->stockService->getAvailableStock($this->item->id, $this->warehouse->id));
    }

    public function test_audit_log_captures_detailed_changes()
    {
        // 0. Setup physical stock
        Stock::create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        // 1. Create a reservation (Generates 'created' audit log)
        $reservation = $this->stockService->reserveStock([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => \App\Models\StockReservation::class,
            'auditable_id' => $reservation->id
        ]);

        // 2. Update reservation status (Generates 'updated' audit log)
        $reservation->update(['status' => 'CANCELLED']);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => \App\Models\StockReservation::class,
            'auditable_id' => $reservation->id,
        ]);

        $log = \App\Models\AuditLog::where('event', 'updated')
            ->where('auditable_id', $reservation->id)
            ->first();

        $this->assertEquals('ACTIVE', $log->old_values['status']);
        $this->assertEquals('CANCELLED', $log->new_values['status']);
    }
}
