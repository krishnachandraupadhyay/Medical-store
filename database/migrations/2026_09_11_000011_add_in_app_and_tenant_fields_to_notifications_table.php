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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('store_id')->constrained('users')->nullOnDelete();
            $table->timestamp('read_at')->nullable()->after('status');
            $table->string('reference_type', 100)->nullable()->after('metadata');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->string('action_url')->nullable()->after('reference_id');
            $table->string('alert_key', 150)->nullable()->after('action_url');

            $table->index(['store_id', 'read_at']);
            $table->index(['store_id', 'alert_key']);
            $table->index(['store_id', 'type']);
            $table->index(['store_id', 'priority']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'read_at']);
            $table->dropIndex(['store_id', 'alert_key']);
            $table->dropIndex(['store_id', 'type']);
            $table->dropIndex(['store_id', 'priority']);
            $table->dropIndex(['reference_type', 'reference_id']);

            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'read_at',
                'reference_type',
                'reference_id',
                'action_url',
                'alert_key',
            ]);
        });
    }
};
