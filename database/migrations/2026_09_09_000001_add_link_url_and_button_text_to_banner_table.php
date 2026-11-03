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
        Schema::table('banner', function (Blueprint $table) {
            if (!Schema::hasColumn('banner', 'link_url')) {
                $table->string('link_url', 500)->nullable()->after('image');
            }
            if (!Schema::hasColumn('banner', 'button_text')) {
                $table->string('button_text', 100)->nullable()->after('link_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner', function (Blueprint $table) {
            if (Schema::hasColumn('banner', 'link_url')) {
                $table->dropColumn('link_url');
            }
            if (Schema::hasColumn('banner', 'button_text')) {
                $table->dropColumn('button_text');
            }
        });
    }
};
