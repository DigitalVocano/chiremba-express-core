<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            if (!Schema::hasColumn('notification', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('notification', 'doctor_id')) {
                $table->unsignedBigInteger('doctor_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('notification', 'title')) {
                $table->string('title')->nullable()->after('doctor_id');
            }
            if (!Schema::hasColumn('notification', 'type')) {
                $table->string('type')->default('general')->after('title');
            }
            if (!Schema::hasColumn('notification', 'related_id')) {
                $table->string('related_id')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notification', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('related_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'doctor_id', 'title', 'type', 'related_id', 'is_read']);
        });
    }
};
