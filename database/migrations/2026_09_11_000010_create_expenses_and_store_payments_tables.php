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
        // 1. Update purchases table with payment tracking columns
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 2)->default(0.00)->after('grand_total');
            $table->string('payment_status', 20)->default('unpaid')->index()->after('paid_amount'); // unpaid, partial, paid
        });

        // 2. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'name']);
            $table->index(['store_id', 'status']);
        });

        // 3. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->string('expense_number', 80);
            $table->date('expense_date')->index();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('cash'); // cash, upi, bank_transfer, card, cheque, other
            $table->string('reference_number', 100)->nullable();
            $table->string('status', 20)->default('paid')->index(); // draft, paid, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'expense_number']);
            $table->index(['store_id', 'status']);
            $table->index(['store_id', 'expense_date']);
        });

        // 4. Store Payments (Supplier, Customer, and Expense payments)
        Schema::create('store_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('payment_number', 80);
            $table->string('type', 30)->index(); // SALE_PAYMENT, PURCHASE_PAYMENT, EXPENSE_PAYMENT
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('payment_date')->index();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30); // cash, upi, bank_transfer, card, cheque, other
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('completed')->index(); // completed, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'payment_number']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'payment_date']);
            $table->index(['purchase_id']);
            $table->index(['sale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_payments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'payment_status']);
        });
    }
};
