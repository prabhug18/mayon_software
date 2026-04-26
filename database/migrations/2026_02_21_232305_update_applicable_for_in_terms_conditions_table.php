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
        Schema::table('terms_conditions', function (Blueprint $table) {
            $table->string('applicable_for')->change();
        });
    }

    public function down(): void
    {
        Schema::table('terms_conditions', function (Blueprint $table) {
            $table->enum('applicable_for', ['flooring', 'civil', 'fabrication', 'networking', 'all'])->change();
        });
    }
};
