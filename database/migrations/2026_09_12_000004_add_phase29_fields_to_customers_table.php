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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('blood_group', 10)->nullable()->after('gender');
            $table->string('emergency_contact_name', 150)->nullable()->after('pincode');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');

            $table->index(['store_id', 'blood_group']);
            $table->index(['store_id', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'blood_group']);
            $table->dropIndex(['store_id', 'city']);
            $table->dropColumn([
                'blood_group',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};
