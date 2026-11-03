<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mobile_diagnostic_logs')) {
            Schema::create('mobile_diagnostic_logs', function (Blueprint $table) {
                $table->id();
                $table->string('category', 50)->default('general')->index(); // crash, fcm, bug_report, api_error, auth, lifecycle
                $table->string('level', 20)->default('info')->index(); // info, warning, error, critical
                $table->string('platform', 30)->nullable()->index(); // ios, android, web
                $table->string('os_version', 60)->nullable();
                $table->string('device_model', 100)->nullable();
                $table->string('app_version', 40)->nullable();
                $table->string('user_role', 30)->nullable()->index(); // patient, doctor, pharmacy, guest
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name', 120)->nullable();
                $table->text('fcm_token')->nullable();
                $table->text('apns_token')->nullable();
                $table->string('permission_status', 60)->nullable();
                $table->string('title', 255)->nullable();
                $table->text('message')->nullable();
                $table->longText('stack_trace')->nullable();
                $table->longText('metadata')->nullable(); // JSON payload
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_resolved')->default(false)->index();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('savetoken')) {
            Schema::table('savetoken', function (Blueprint $table) {
                if (!Schema::hasColumn('savetoken', 'platform')) {
                    $table->string('platform', 30)->nullable()->after('type');
                }
                if (!Schema::hasColumn('savetoken', 'apns_token')) {
                    $table->text('apns_token')->nullable()->after('platform');
                }
                if (!Schema::hasColumn('savetoken', 'device_model')) {
                    $table->string('device_model', 100)->nullable()->after('apns_token');
                }
                if (!Schema::hasColumn('savetoken', 'app_version')) {
                    $table->string('app_version', 40)->nullable()->after('device_model');
                }
                if (!Schema::hasColumn('savetoken', 'permission_status')) {
                    $table->string('permission_status', 60)->nullable()->after('app_version');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_diagnostic_logs');
    }
};

