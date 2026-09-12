<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->morphs('notable'); // notable_type + notable_id
            $table->enum('type', ['note', 'call', 'meeting', 'email', 'followup'])->default('note');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->date('follow_up_date')->nullable();
            $table->boolean('follow_up_done')->default(false);
            $table->timestamp('follow_up_done_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['store_id', 'notable_type', 'notable_id']);
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
    }
};
