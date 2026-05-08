<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Division;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseRack;
use App\Models\Unit;
use App\Models\ItemCategory;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Organization
        $company = Company::create(['name' => 'True Stock Corp', 'code' => 'TSC', 'address' => 'Corporate St 123']);
        $division = Division::create(['company_id' => $company->id, 'name' => 'Logistics Division', 'code' => 'LOG']);
        $warehouse = Warehouse::create(['division_id' => $division->id, 'name' => 'Main Warehouse', 'code' => 'WH01', 'address' => 'Warehouse Ave 456']);
        
        $location = WarehouseLocation::create(['warehouse_id' => $warehouse->id, 'name' => 'Zone A', 'code' => 'ZONE-A']);
        WarehouseRack::create(['location_id' => $location->id, 'name' => 'Rack A1', 'code' => 'A1']);
        WarehouseRack::create(['location_id' => $location->id, 'name' => 'Rack A2', 'code' => 'A2']);

        // Units
        Unit::create(['name' => 'Piece', 'code' => 'PCS']);
        Unit::create(['name' => 'Kilogram', 'code' => 'KG']);
        Unit::create(['name' => 'Box', 'code' => 'BOX']);

        // Categories
        ItemCategory::create(['name' => 'Raw Material', 'code' => 'RAW']);
        ItemCategory::create(['name' => 'Finished Goods', 'code' => 'FG']);
    }
}
