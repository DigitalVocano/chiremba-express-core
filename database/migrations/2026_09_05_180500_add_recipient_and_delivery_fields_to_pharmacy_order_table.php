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
        Schema::table('pharmacy_order', function (Blueprint $table) {
            if (!Schema::hasColumn('pharmacy_order', 'recipient_name')) {
                $table->string('recipient_name', 255)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('pharmacy_order', 'recipient_phone')) {
                $table->string('recipient_phone', 50)->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('pharmacy_order', 'delivery_mode')) {
                $table->string('delivery_mode', 50)->default('pointlink')->after('recipient_phone');
            }
            if (!Schema::hasColumn('pharmacy_order', 'pointlink_ride_id')) {
                $table->bigInteger('pointlink_ride_id')->nullable()->after('delivery_mode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_order', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'recipient_phone',
                'delivery_mode',
                'pointlink_ride_id'
            ]);
        });
    }
};
