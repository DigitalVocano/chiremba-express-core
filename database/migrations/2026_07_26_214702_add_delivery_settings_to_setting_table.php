<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliverySettingsToSettingTable extends Migration
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
                if (!Schema::hasColumn('setting', 'is_whatsapp_enabled')) {
                    $table->tinyInteger('is_whatsapp_enabled')->default(1);
                }
                if (!Schema::hasColumn('setting', 'default_delivery_partner')) {
                    $table->string('default_delivery_partner')->nullable();
                }
                if (!Schema::hasColumn('setting', 'linkgrid_api_endpoint')) {
                    $table->string('linkgrid_api_endpoint')->nullable();
                }
                if (!Schema::hasColumn('setting', 'linkgrid_api_token')) {
                    $table->text('linkgrid_api_token')->nullable();
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
                'is_whatsapp_enabled',
                'default_delivery_partner',
                'linkgrid_api_endpoint',
                'linkgrid_api_token'
            ]);
        });
    }
}
