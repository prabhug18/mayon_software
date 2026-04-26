<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('restrict');
            $table->foreignId('service_item_id')->constrained('service_items')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->string('unit', 10);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('base_cost', 12, 2)->default(0)->comment('Internal or vendor cost');
            $table->enum('margin_type', ['PERCENTAGE', 'FIXED'])->default('PERCENTAGE');
            $table->decimal('margin_value', 12, 2)->default(0);
            $table->decimal('selling_rate', 12, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(18.00);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
