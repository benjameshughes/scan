<?php

use App\Models\Product;
use App\Models\User;
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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Product information
            $table->foreignIdFor(Product::class)->constrained();

            // Location information
            $table->string('from_location_id')->nullable(); // Linnworks location ID
            $table->string('to_location_id')->nullable(); // Linnworks location ID
            $table->string('from_location_code')->nullable(); // For display/history
            $table->string('to_location_code')->nullable(); // For display/history

            // Movement details
            $table->integer('quantity');
            $table->string('type'); // bay_refill, manual_transfer, scan_adjustment, etc.
            $table->string('reference_type')->nullable(); // scan, transfer, manual
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of related scan/transfer

            // User tracking
            $table->foreignIdFor(User::class)->constrained();

            // Additional information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For any additional data

            // Timestamps
            $table->timestamp('moved_at'); // When the movement occurred
            $table->timestamps();

            // Indexes for performance
            $table->index(['product_id', 'moved_at']);
            $table->index(['from_location_id', 'moved_at']);
            $table->index(['to_location_id', 'moved_at']);
            $table->index(['type', 'moved_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
