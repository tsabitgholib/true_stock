<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // GRN
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('receive_date');
            $table->string('status')->default('DRAFT'); // DRAFT, SUBMITTED, COMPLETED
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('goods_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->decimal('quantity', 16, 4);
            $table->timestamps();
        });

        // GIN
        Schema::create('goods_issues', function (Blueprint $table) {
            $table->id();
            $table->string('gin_number')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('issue_date');
            $table->string('status')->default('DRAFT');
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('goods_issue_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->foreignId('stock_id')->constrained(); // Linked to specific stock bucket
            $table->decimal('quantity', 16, 4);
            $table->timestamps();
        });

        // Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->string('status')->default('PENDING'); // PENDING, IN_TRANSIT, COMPLETED, CANCELLED
            $table->foreignId('user_id')->constrained();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('transfer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches');
            $table->foreignId('from_location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('from_rack_id')->nullable()->constrained('warehouse_racks');
            $table->foreignId('to_location_id')->nullable()->constrained('warehouse_locations');
            $table->foreignId('to_rack_id')->nullable()->constrained('warehouse_racks');
            $table->decimal('quantity', 16, 4);
            $table->timestamps();
        });

        // Adjustments
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('reason');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->foreignId('user_id')->constrained();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adjustments');
        Schema::dropIfExists('transfer_details');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('goods_issue_details');
        Schema::dropIfExists('goods_issues');
        Schema::dropIfExists('goods_receipt_details');
        Schema::dropIfExists('goods_receipts');
    }
};
