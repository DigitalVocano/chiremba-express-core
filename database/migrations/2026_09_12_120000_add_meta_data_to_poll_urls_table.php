<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetaDataToPollUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('poll_urls')) {
            Schema::table('poll_urls', function (Blueprint $table) {
                if (!Schema::hasColumn('poll_urls', 'meta_data')) {
                    $table->longText('meta_data')->nullable()->after('status');
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
        if (Schema::hasTable('poll_urls')) {
            Schema::table('poll_urls', function (Blueprint $table) {
                if (Schema::hasColumn('poll_urls', 'meta_data')) {
                    $table->dropColumn('meta_data');
                }
            });
        }
    }
}
