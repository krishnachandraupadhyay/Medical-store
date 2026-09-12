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
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'status']);
            $table->index('is_system');
        });

        // 2. Permissions Table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->index(); // Dashboard, Customers, Suppliers, Medicines, Inventory, Sales, Purchases, Payments, Expenses, Reports, Notifications, Staff, Settings
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role Permissions Pivot Table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');

            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Update Users Table with role_id, last_login_at, created_by
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->foreignId('created_by')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['role_id', 'last_login_at', 'created_by']);
        });

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
