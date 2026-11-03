<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDemoToUsersAndDoctors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('patient') && !Schema::hasColumn('patient', 'is_demo')) {
            Schema::table('patient', function (Blueprint $table) {
                $table->boolean('is_demo')->default(0)->after('id');
            });
        }

        if (Schema::hasTable('patients') && !Schema::hasColumn('patients', 'is_demo')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->boolean('is_demo')->default(0)->after('id');
            });
        }

        if (Schema::hasTable('doctors') && !Schema::hasColumn('doctors', 'is_demo')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->boolean('is_demo')->default(0)->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('patients') && Schema::hasColumn('patients', 'is_demo')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }

        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'is_demo')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('is_demo');
            });
        }
    }
}
