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
            $table->dropForeign(['eligibility_id']);
        });

        Schema::rename('eligibilities', 'methodologies');

        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('eligibility_id', 'methodology_id');
            $table->renameColumn('eligibility_content', 'methodology_content');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('methodology_id')->references('id')->on('methodologies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['methodology_id']);
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->renameColumn('methodology_id', 'eligibility_id');
            $table->renameColumn('methodology_content', 'eligibility_content');
        });

        Schema::rename('methodologies', 'eligibilities');

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreign('eligibility_id')->references('id')->on('eligibilities')->onDelete('set null');
        });
    }
};
