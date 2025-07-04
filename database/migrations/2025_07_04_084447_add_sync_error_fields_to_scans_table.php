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
        Schema::table('scans', function (Blueprint $table) {
            // Add error tracking fields
            $table->text('sync_error_message')->nullable()->after('sync_status');
            $table->string('sync_error_type')->nullable()->after('sync_error_message'); // network, auth, rate_limit, product_not_found, etc.
            $table->integer('sync_attempts')->default(0)->after('sync_error_type'); // Track retry attempts
            $table->timestamp('last_sync_attempt')->nullable()->after('sync_attempts');
            $table->timestamp('synced_at')->nullable()->after('last_sync_attempt'); // When successfully synced
            $table->json('sync_metadata')->nullable()->after('synced_at'); // Store additional sync context
            
            // Add notes field for manual annotations
            $table->text('notes')->nullable()->after('sync_metadata');
            
            // Add indexes for performance
            $table->index('sync_error_type');
            $table->index('sync_attempts');
            $table->index('last_sync_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropIndex(['sync_error_type']);
            $table->dropIndex(['sync_attempts']);
            $table->dropIndex(['last_sync_attempt']);
            
            $table->dropColumn([
                'sync_error_message',
                'sync_error_type', 
                'sync_attempts',
                'last_sync_attempt',
                'synced_at',
                'sync_metadata',
                'notes'
            ]);
        });
    }
};
