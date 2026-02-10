<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakePurchaseOrderPriceColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Make amount, unit_price and total nullable using raw SQL to avoid doctrine/dbal requirement
        DB::statement("ALTER TABLE `purchase_order_items` MODIFY `unit_price` DECIMAL(18,2) NULL");
        DB::statement("ALTER TABLE `purchase_order_items` MODIFY `total` DECIMAL(18,2) NULL");
        DB::statement("ALTER TABLE `purchase_orders` MODIFY `amount` DECIMAL(18,2) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `purchase_order_items` MODIFY `unit_price` DECIMAL(18,2) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `purchase_order_items` MODIFY `total` DECIMAL(18,2) NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE `purchase_orders` MODIFY `amount` DECIMAL(18,2) NOT NULL DEFAULT 0");
    }
}
