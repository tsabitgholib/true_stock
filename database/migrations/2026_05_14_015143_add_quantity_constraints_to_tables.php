<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For stocks table
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned()->change();
            $table->decimal('reserved_quantity', 16, 4)->unsigned()->change();
        });

        // For stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned()->change();
        });

        // For stock_reservations
        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned()->change();
        });
        
        $dbDriver = DB::getDriverName();
        
        // SQLite doesn't support ALTER TABLE ADD CONSTRAINT. 
        // MySQL 8.0.16+ and PostgreSQL do.
        if (in_array($dbDriver, ['mysql', 'pgsql'])) {
            DB::statement("ALTER TABLE stocks ADD CONSTRAINT quantity_non_negative CHECK (quantity >= 0)");
            DB::statement("ALTER TABLE stocks ADD CONSTRAINT reserved_quantity_non_negative CHECK (reserved_quantity >= 0)");
            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT movement_quantity_non_negative CHECK (quantity >= 0)");
            DB::statement("ALTER TABLE stock_reservations ADD CONSTRAINT reservation_quantity_non_negative CHECK (quantity >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dbDriver = DB::getDriverName();
        if (in_array($dbDriver, ['mysql', 'pgsql'])) {
            DB::statement("ALTER TABLE stocks DROP CONSTRAINT quantity_non_negative");
            DB::statement("ALTER TABLE stocks DROP CONSTRAINT reserved_quantity_non_negative");
            DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT movement_quantity_non_negative");
            DB::statement("ALTER TABLE stock_reservations DROP CONSTRAINT reservation_quantity_non_negative");
        }

        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned(false)->change();
            $table->decimal('reserved_quantity', 16, 4)->unsigned(false)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned(false)->change();
        });

        Schema::table('stock_reservations', function (Blueprint $table) {
            $table->decimal('quantity', 16, 4)->unsigned(false)->change();
        });
    }
};
