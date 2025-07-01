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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_id')->unique()->comment('Linnworks location ID (GUID)');
            $table->string('code')->comment('Location code like "11A", "12B-3"');
            $table->string('name')->nullable()->comment('Display name for location');
            $table->integer('use_count')->default(0)->comment('Number of times used (for frecency)');
            $table->timestamp('last_used_at')->nullable()->comment('Last time used (for recency)');
            $table->boolean('is_active')->default(true)->comment('Whether location is active');
            $table->string('qr_code')->nullable()->comment('QR code for location (optional)');
            $table->timestamps();
            
            $table->index(['is_active', 'last_used_at']);
            $table->index(['use_count', 'last_used_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
