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
        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('cascade'); // If null, global conversion
            $table->foreignId('from_unit_id')->constrained('units');
            $table->foreignId('to_unit_id')->constrained('units');
            $table->decimal('factor', 16, 8); // from_unit * factor = to_unit
            $table->timestamps();
            
            $table->unique(['item_id', 'from_unit_id', 'to_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};
