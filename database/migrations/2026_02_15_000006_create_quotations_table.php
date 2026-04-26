<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->onDelete('set null');
            $table->foreignId('company_id')->constrained('companies')->onDelete('restrict');
            $table->string('quotation_no')->unique();
            $table->date('quotation_date');
            $table->date('valid_till')->nullable();
            $table->enum('quotation_type', ['OWN', 'THIRD_PARTY', 'MIXED'])->default('OWN');
            $table->foreignId('terms_condition_id')->nullable()->constrained('terms_conditions')->onDelete('set null');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('gst_total', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->enum('status', ['DRAFT', 'SENT', 'APPROVED', 'REVISED'])->default('DRAFT');
            $table->foreignId('parent_quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
            $table->integer('revision_no')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
