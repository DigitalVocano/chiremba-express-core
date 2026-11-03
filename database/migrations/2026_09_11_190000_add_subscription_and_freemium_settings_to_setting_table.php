<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionAndFreemiumSettingsToSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setting', function (Blueprint $table) {
            if (!Schema::hasColumn('setting', 'is_doctor_subscription_enabled')) {
                $table->tinyInteger('is_doctor_subscription_enabled')->default(1);
            }
            if (!Schema::hasColumn('setting', 'is_patient_subscription_enabled')) {
                $table->tinyInteger('is_patient_subscription_enabled')->default(1);
            }
            if (!Schema::hasColumn('setting', 'is_pharmacy_subscription_enabled')) {
                $table->tinyInteger('is_pharmacy_subscription_enabled')->default(1);
            }
            if (!Schema::hasColumn('setting', 'freemium_doctor_appointment_limit')) {
                $table->integer('freemium_doctor_appointment_limit')->default(0); // 0 = unlimited free
            }
            if (!Schema::hasColumn('setting', 'freemium_patient_consultation_limit')) {
                $table->integer('freemium_patient_consultation_limit')->default(0); // 0 = unlimited free
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('setting', function (Blueprint $table) {
            $table->dropColumn([
                'freemium_doctor_appointment_limit',
                'freemium_patient_consultation_limit'
            ]);
        });
    }
}
