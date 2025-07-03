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
        Schema::create('sync_progress', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('manual_sync'); // manual_sync, daily_sync, etc.
            $table->string('status')->default('running'); // running, completed, failed
            $table->json('stats')->nullable(); // Current statistics
            $table->json('current_batch')->nullable(); // Current batch info
            $table->text('current_operation')->nullable(); // What's happening now
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_progress');
    }
};
