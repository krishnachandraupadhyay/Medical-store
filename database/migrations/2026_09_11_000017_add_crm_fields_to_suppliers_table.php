<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('notes');
            $table->timestamp('last_contacted_at')->nullable()->after('tags');
            $table->decimal('credit_limit', 12, 2)->nullable()->after('last_contacted_at');
            $table->string('payment_terms', 100)->nullable()->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['tags', 'last_contacted_at', 'credit_limit', 'payment_terms']);
        });
    }
};
