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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Sync tracking fields (similar to scans table)
            $table->string('sync_status')->default('pending')->after('moved_at');
            $table->integer('sync_attempts')->default(0)->after('sync_status');
            $table->timestamp('last_sync_attempt_at')->nullable()->after('sync_attempts');
            $table->timestamp('processed_at')->nullable()->after('last_sync_attempt_at');
            $table->text('sync_error_message')->nullable()->after('processed_at');
            $table->string('sync_error_type')->nullable()->after('sync_error_message');
            
            // Add index for querying by sync status
            $table->index('sync_status');
            $table->index(['sync_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['sync_status', 'created_at']);
            $table->dropIndex(['sync_status']);
            
            $table->dropColumn([
                'sync_status',
                'sync_attempts', 
                'last_sync_attempt_at',
                'processed_at',
                'sync_error_message',
                'sync_error_type',
            ]);
        });
    }
};