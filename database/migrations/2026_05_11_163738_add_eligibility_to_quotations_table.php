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
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('eligibility_id')->nullable()->after('terms_content')->constrained('eligibilities')->onDelete('set null');
            $table->text('eligibility_content')->nullable()->after('eligibility_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['eligibility_id']);
            $table->dropColumn(['eligibility_id', 'eligibility_content']);
        });
    }
};
