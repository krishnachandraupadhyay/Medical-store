<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('loyalty_tier', ['regular', 'silver', 'gold', 'platinum'])
                ->default('regular')
                ->after('status');
            $table->json('tags')->nullable()->after('loyalty_tier');
            $table->timestamp('last_contacted_at')->nullable()->after('tags');
            $table->unsignedInteger('visit_count')->default(0)->after('last_contacted_at');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['loyalty_tier', 'tags', 'last_contacted_at', 'visit_count']);
        });
    }
};
