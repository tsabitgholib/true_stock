<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->decimal('quantity', 16, 4);
            $table->string('status')->default('ACTIVE'); // ACTIVE, FULFILLED, CANCELLED, EXPIRED
            $table->string('reference_type')->nullable(); // e.g., SalesOrder, ProductionOrder
            $table->string('reference_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['item_id', 'warehouse_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
