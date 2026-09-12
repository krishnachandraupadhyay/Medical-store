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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('stock_count_number', 60);
            $table->date('count_date')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->integer('total_items')->default(0);
            $table->integer('matched_items')->default(0);
            $table->integer('discrepant_items')->default(0);
            $table->integer('total_variance_quantity')->default(0);
            $table->decimal('total_variance_cost', 12, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'stock_count_number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'count_date']);
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->integer('system_quantity')->default(0);
            $table->integer('physical_quantity')->default(0);
            $table->integer('variance_quantity')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('variance_cost', 12, 2)->default(0.00);
            $table->string('adjustment_type', 30)->nullable(); // none, surplus, shortage
            $table->string('reason', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_count_id', 'batch_id']);
            $table->index(['store_id', 'medicine_id']);
            $table->unique(['stock_count_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_counts');
    }
};
