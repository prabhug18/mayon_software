<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_vendor_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_item_id')->constrained('quotation_items')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('restrict');
            $table->decimal('vendor_rate', 12, 2)->default(0);
            $table->decimal('vendor_total', 14, 2)->default(0);
            $table->decimal('vendor_gst', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_vendor_costs');
    }
};
