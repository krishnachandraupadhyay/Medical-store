<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_code', 50)->nullable()->after('store_id');
            $table->string('pan_number', 20)->nullable()->after('gst_number');
            $table->decimal('opening_balance', 12, 2)->default(0.00)->after('pan_number');
            $table->string('opening_balance_type', 20)->default('payable')->after('opening_balance'); // 'payable', 'advance'

            $table->index(['store_id', 'supplier_code']);
        });

        // Backfill existing suppliers with unique codes per store
        $suppliers = DB::table('suppliers')->whereNull('supplier_code')->orderBy('id')->get();
        $storeCounters = [];

        foreach ($suppliers as $s) {
            $storeId = $s->store_id;
            if (! isset($storeCounters[$storeId])) {
                $storeCounters[$storeId] = 1;
            } else {
                $storeCounters[$storeId]++;
            }

            $code = 'SUP-'.str_pad((string) $storeCounters[$storeId], 6, '0', STR_PAD_LEFT);
            DB::table('suppliers')->where('id', $s->id)->update(['supplier_code' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'supplier_code']);
            $table->dropColumn([
                'supplier_code',
                'pan_number',
                'opening_balance',
                'opening_balance_type',
            ]);
        });
    }
};
