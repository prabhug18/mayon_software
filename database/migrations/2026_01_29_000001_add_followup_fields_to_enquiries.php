<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->timestamp('next_follow_up_at')->nullable()->after('source_id');
            $table->text('reminder_notes')->nullable()->after('next_follow_up_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('reminder_notes');
        });
    }

    public function down()
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['next_follow_up_at','reminder_notes','reminder_sent_at']);
        });
    }
};
