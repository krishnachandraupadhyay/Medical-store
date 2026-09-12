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
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->decimal('refund_amount', 12, 2)->default(0.00)->after('grand_total');
            $table->decimal('adjustment_amount', 12, 2)->default(0.00)->after('refund_amount');
            $table->string('refund_method', 50)->nullable()->after('adjustment_amount');
            $table->string('refund_status', 30)->default('completed')->after('refund_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn([
                'refund_amount',
                'adjustment_amount',
                'refund_method',
                'refund_status',
            ]);
        });
    }
};
