<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'uom_id')) {
                $table->unsignedBigInteger('uom_id')->nullable()->after('product_id');
                $table->foreign('uom_id')->references('id')->on('uoms')->onDelete('set null');
            }
        });

        // Backfill: if there is an existing 'uom' string column, try to match by name
        if (Schema::hasColumn('purchase_order_items', 'uom')) {
            $rows = DB::table('purchase_order_items')->select('id', 'uom')->whereNotNull('uom')->get();
            foreach ($rows as $r) {
                $uom = DB::table('uoms')->where('name', $r->uom)->first();
                if ($uom) {
                    DB::table('purchase_order_items')->where('id', $r->id)->update(['uom_id' => $uom->id]);
                }
            }

            // drop old uom string column
            Schema::table('purchase_order_items', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_order_items', 'uom')) {
                    $table->dropColumn('uom');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'uom')) {
                $table->string('uom', 64)->nullable()->after('product_id');
            }
            if (Schema::hasColumn('purchase_order_items', 'uom_id')) {
                $table->dropForeign(['uom_id']);
                $table->dropColumn('uom_id');
            }
        });
    }
};
