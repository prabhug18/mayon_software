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
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->change();
            $table->foreignId('service_item_id')->nullable()->change();
            $table->string('manual_service_name')->nullable()->after('service_item_id');
            $table->string('manual_item_name')->nullable()->after('manual_service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable(false)->change();
            $table->foreignId('service_item_id')->nullable(false)->change();
            $table->dropColumn(['manual_service_name', 'manual_item_name']);
        });
    }
};
