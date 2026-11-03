<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add profile_views and search_impressions to doctors table if missing
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'profile_views')) {
                $table->unsignedBigInteger('profile_views')->default(0)->after('is_approve');
            }
            if (!Schema::hasColumn('doctors', 'search_impressions')) {
                $table->unsignedBigInteger('search_impressions')->default(0)->after('profile_views');
            }
        });

        // 2. Create provider_profile_views analytics log table
        if (!Schema::hasTable('provider_profile_views')) {
            Schema::create('provider_profile_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doctor_id')->index();
                $table->unsignedBigInteger('viewer_id')->nullable()->index();
                $table->string('viewer_ip', 45)->nullable();
                $table->string('platform', 30)->default('mobile');
                $table->string('user_agent')->nullable();
                $table->date('view_date')->index();
                $table->timestamps();
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
        Schema::dropIfExists('provider_profile_views');
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'profile_views')) {
                $table->dropColumn('profile_views');
            }
            if (Schema::hasColumn('doctors', 'search_impressions')) {
                $table->dropColumn('search_impressions');
            }
        });
    }
};
