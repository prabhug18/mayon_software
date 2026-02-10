<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquiryFollowUpsTable extends Migration
{
    public function up()
    {
        Schema::create('enquiry_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enquiry_id');
            $table->timestamp('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('enquiry_id')->references('id')->on('enquiries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiry_follow_ups');
    }
}
