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
        Schema::table('products', function (Blueprint $table) {
            $table->string('linnworks_id')->nullable()->index()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('auto_synced')->default(false)->after('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['linnworks_id', 'last_synced_at', 'auto_synced']);
        });
    }
};
