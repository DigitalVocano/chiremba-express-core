<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoFactorAuthToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'two_factor_secret')) {
                    $table->string('two_factor_secret')->nullable()->after('password');
                }
                if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
                }
                if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_enabled');
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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'two_factor_secret')) {
                    $table->dropColumn('two_factor_secret');
                }
                if (Schema::hasColumn('users', 'two_factor_enabled')) {
                    $table->dropColumn('two_factor_enabled');
                }
                if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                    $table->dropColumn('two_factor_recovery_codes');
                }
            });
        }
    }
}
