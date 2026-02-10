<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropItemsFromPurchaseOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'items')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('items');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'items')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->json('items')->nullable()->after('amount');
            });
        }
    }
}
