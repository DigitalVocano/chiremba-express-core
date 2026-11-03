<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptMedicalAidToDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('doctors') && !Schema::hasColumn('doctors', 'accept_medical_aid')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->tinyInteger('accept_medical_aid')->default(1)->after('accept_digital_payments');
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
        if (Schema::hasTable('doctors') && Schema::hasColumn('doctors', 'accept_medical_aid')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('accept_medical_aid');
            });
        }
    }
}
