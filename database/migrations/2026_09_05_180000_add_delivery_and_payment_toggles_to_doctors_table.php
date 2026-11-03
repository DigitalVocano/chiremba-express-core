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
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'delivery_mode')) {
                $table->string('delivery_mode', 50)->default('pointlink')->after('lon');
            }
            if (!Schema::hasColumn('doctors', 'self_delivery_fee')) {
                $table->decimal('self_delivery_fee', 8, 2)->default(0.00)->after('delivery_mode');
            }
            if (!Schema::hasColumn('doctors', 'has_own_bikes')) {
                $table->tinyInteger('has_own_bikes')->default(0)->after('self_delivery_fee');
            }
            if (!Schema::hasColumn('doctors', 'accept_pointlink_deliveries')) {
                $table->tinyInteger('accept_pointlink_deliveries')->default(1)->after('has_own_bikes');
            }
            if (!Schema::hasColumn('doctors', 'accept_cod')) {
                $table->tinyInteger('accept_cod')->default(1)->after('accept_pointlink_deliveries');
            }
            if (!Schema::hasColumn('doctors', 'accept_digital_payments')) {
                $table->tinyInteger('accept_digital_payments')->default(1)->after('accept_cod');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_mode',
                'self_delivery_fee',
                'has_own_bikes',
                'accept_pointlink_deliveries',
                'accept_cod',
                'accept_digital_payments'
            ]);
        });
    }
};
