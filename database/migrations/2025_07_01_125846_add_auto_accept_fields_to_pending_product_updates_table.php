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
        Schema::table('pending_product_updates', function (Blueprint $table) {
            // Add auto_accepted to the status enum
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_applied', 'auto_accepted'])
                ->default('pending')
                ->change();

            // Add fields for auto-acceptance tracking
            $table->foreignId('accepted_by')->nullable()->constrained('users')->after('reviewed_at');
            $table->timestamp('accepted_at')->nullable()->after('accepted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_product_updates', function (Blueprint $table) {
            // Remove auto-acceptance fields
            $table->dropForeign(['accepted_by']);
            $table->dropColumn(['accepted_by', 'accepted_at']);

            // Revert status enum to original values
            $table->enum('status', ['pending', 'approved', 'rejected', 'auto_applied'])
                ->default('pending')
                ->change();
        });
    }
};
