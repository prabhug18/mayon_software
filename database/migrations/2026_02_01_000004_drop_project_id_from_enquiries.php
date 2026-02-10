<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DropProjectIdFromEnquiries extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('enquiries')) return;

        if (Schema::hasColumn('enquiries', 'project_id')) {
            // try drop foreign key if exists
            Schema::table('enquiries', function (Blueprint $table) {
                try {
                    $table->dropForeign(['project_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key doesn't exist
                }
                try {
                    $table->dropColumn('project_id');
                } catch (\Exception $e) {
                    // ignore drop errors
                }
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('enquiries')) return;

        Schema::table('enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiries', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('enquiry_type_id');
                try {
                    $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
                } catch (\Exception $e) {
                    // ignore if cannot add foreign key
                }
            }
        });
    }
}
