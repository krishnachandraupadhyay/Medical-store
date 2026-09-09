<?php

use App\Enums\StoreStatus;
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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->index();
            $table->string('logo')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('mobile')->index();
            $table->string('alternate_mobile')->nullable();

            // Address Information
            $table->text('address')->nullable();
            $table->string('city')->index();
            $table->string('state')->index();
            $table->string('pincode', 20);

            // Business & License Information
            $table->string('gstin', 30)->nullable();
            $table->string('drug_license_no', 100)->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('store_type', 50)->default('Retail');

            // Status & Timestamps
            $table->string('status', 30)->default(StoreStatus::ACTIVE->value)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
