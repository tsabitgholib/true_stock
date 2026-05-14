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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, CANCELLED
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->decimal('current_quantity', 16, 4);
            $table->decimal('new_quantity', 16, 4);
            $table->decimal('diff_quantity', 16, 4);
            $table->string('reason_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
        Schema::dropIfExists('stock_adjustments');
    }
};
