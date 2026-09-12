<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Hold Bill reference — identifies a named held draft bill in POS (e.g. HOLD-001)
            $table->string('hold_reference', 50)->nullable()->after('notes');

            // Index for quick lookup of held bills per store
            $table->index(['store_id', 'hold_reference'], 'sales_store_hold_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_store_hold_reference_index');
            $table->dropColumn('hold_reference');
        });
    }
};
