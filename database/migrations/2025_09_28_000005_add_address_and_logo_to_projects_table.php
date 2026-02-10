<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressAndLogoToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'address')) {
                $table->string('address')->nullable()->after('location');
            }
            if (!Schema::hasColumn('projects', 'logo_image')) {
                $table->string('logo_image')->nullable()->after('address');
            }
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'logo_image')) {
                $table->dropColumn('logo_image');
            }
            if (Schema::hasColumn('projects', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
}
