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
            $table->string('fb_lead_id')->nullable()->unique()->after('id');
            $table->string('fb_campaign_name')->nullable()->after('source_id');
            $table->string('fb_form_name')->nullable()->after('fb_campaign_name');
            $table->string('fb_platform')->nullable()->after('fb_form_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['fb_lead_id', 'fb_campaign_name', 'fb_form_name', 'fb_platform']);
        });
    }
};
