<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DeduplicateEnquiryFollowUpsAndAddUniqueIndex extends Migration
{
    public function up()
    {
        // remove exact duplicates (same enquiry_id and scheduled_at), keep lowest id
        // This raw query deletes rows where another row exists with the same enquiry_id and scheduled_at and a smaller id
        DB::statement("DELETE e1 FROM enquiry_follow_ups e1 JOIN enquiry_follow_ups e2 ON e1.enquiry_id = e2.enquiry_id AND ((e1.scheduled_at IS NULL AND e2.scheduled_at IS NULL) OR (e1.scheduled_at = e2.scheduled_at)) AND e1.id > e2.id");

        Schema::table('enquiry_follow_ups', function (Blueprint $table) {
            // create unique index to prevent future duplicates
            $table->unique(['enquiry_id','scheduled_at'], 'enquiry_followups_enquiry_scheduled_unique');
        });
    }

    public function down()
    {
        Schema::table('enquiry_follow_ups', function (Blueprint $table) {
            $table->dropUnique('enquiry_followups_enquiry_scheduled_unique');
        });
    }
}
