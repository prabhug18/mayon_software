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
        Schema::table('enquiries', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('source_id');
            $table->unsignedBigInteger('service_item_id')->nullable()->after('service_id');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
            $table->foreign('service_item_id')->references('id')->on('service_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['service_item_id']);
            $table->dropColumn(['service_id', 'service_item_id']);
        });
    }
};
