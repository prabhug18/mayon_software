<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders','site_engineer_id')) {
                $table->foreignId('site_engineer_id')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders','site_engineer_id')) {
                $table->dropForeign(['site_engineer_id']);
                $table->dropColumn('site_engineer_id');
            }
        });
    }
};
