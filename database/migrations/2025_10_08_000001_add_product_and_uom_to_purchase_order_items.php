<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('purchase_order_items', 'uom')) {
                $table->string('uom', 64)->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order_items', 'uom')) {
                $table->dropColumn('uom');
            }
            if (Schema::hasColumn('purchase_order_items', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });
    }
};
