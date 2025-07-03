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
            // Add location tracking to scans
            $table->string('location_id')->nullable()->after('action'); // Linnworks location ID
            $table->string('location_code')->nullable()->after('location_id'); // For display/history
            
            // Index for performance
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropIndex(['location_id']);
            $table->dropColumn(['location_id', 'location_code']);
        });
    }
};