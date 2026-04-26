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
            $table->string('email')->nullable()->after('name');
            $table->string('priority')->default('Medium')->after('status');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->after('priority');
            $table->string('gstin')->nullable()->after('location');
            $table->text('address')->nullable()->after('gstin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['email', 'priority', 'assigned_to', 'gstin', 'address']);
        });
    }
};
