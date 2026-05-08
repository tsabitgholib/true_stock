<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->timestamps();
            $table->unique(['item_id', 'batch_number']);
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->decimal('quantity', 16, 4)->default(0);
            $table->decimal('reserved_quantity', 16, 4)->default(0);
            $table->timestamps();
            
            $table->unique(['item_id', 'warehouse_id', 'location_id', 'rack_id', 'batch_id'], 'stock_unique_index');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->string('movement_type'); // STOCK_IN, STOCK_OUT, TRANSFER, ADJUSTMENT, RETURN, SCRAP
            $table->decimal('quantity', 16, 4);
            $table->string('reference_type')->nullable(); // GRN, GIN, Transfer, Adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('stock_id')->constrained();
            $table->string('serial_number')->unique();
            $table->string('status')->default('IN_STOCK'); // IN_STOCK, SOLD, TRANSIT, SCRAPPED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_serials');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('stock_batches');
    }
};
