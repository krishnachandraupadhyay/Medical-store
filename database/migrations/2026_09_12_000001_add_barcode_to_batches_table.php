<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Barcode support — batch-level since each batch can have a unique barcode
            $table->string('barcode', 100)->nullable()->after('batch_number');
            $table->string('secondary_barcode', 100)->nullable()->after('barcode');

            // Indexes for fast barcode lookup (store-scoped)
            $table->index(['store_id', 'barcode'], 'batches_store_barcode_index');
            $table->index(['store_id', 'secondary_barcode'], 'batches_store_secondary_barcode_index');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex('batches_store_barcode_index');
            $table->dropIndex('batches_store_secondary_barcode_index');
            $table->dropColumn(['barcode', 'secondary_barcode']);
        });
    }
};
