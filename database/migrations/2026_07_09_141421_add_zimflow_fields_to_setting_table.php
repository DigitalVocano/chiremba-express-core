<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZimflowFieldsToSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('setting')) {
            Schema::table('setting', function (Blueprint $table) {
                if (!Schema::hasColumn('setting', 'zimflow_api_token')) {
                    $table->text('zimflow_api_token')->nullable();
                }
                if (!Schema::hasColumn('setting', 'zimflow_api_endpoint')) {
                    $table->string('zimflow_api_endpoint')->default('https://zimflow.co.zw/public-api/wpbox/');
                }
                if (!Schema::hasColumn('setting', 'zimflow_auth_template')) {
                    $table->string('zimflow_auth_template')->default('pointlink_otp');
                }
                if (!Schema::hasColumn('setting', 'zimflow_template_language')) {
                    $table->string('zimflow_template_language')->default('en');
                }
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
        Schema::table('setting', function (Blueprint $table) {
            $table->dropColumn([
                'zimflow_api_token',
                'zimflow_api_endpoint',
                'zimflow_auth_template',
                'zimflow_template_language'
            ]);
        });
    }
}
