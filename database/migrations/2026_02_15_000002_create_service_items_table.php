<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('hsn_sac_code')->nullable();
            $table->enum('unit', ['SQM', 'RMT', 'SFT', 'NOS', 'LS'])->default('SQM');
            $table->decimal('default_rate', 12, 2)->default(0);
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
