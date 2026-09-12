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
        // 1. Categories Master
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'status']);
        });

        // 2. Manufacturers / Pharmaceutical Companies Master
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'status']);
        });

        // 3. Dosage Forms Master (Tablet, Capsule, Syrup, etc.)
        Schema::create('dosage_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('short_name', 30)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });

        // 4. Units Master (Strip, Bottle, Box, Piece, etc.)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('short_name', 30)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('dosage_forms');
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('categories');
    }
};
