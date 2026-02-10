<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('mobile')->nullable();
            $table->string('name')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('enquiry_type_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('enquiry_type_id')->references('id')->on('enquiry_types')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiries');
    }
};
