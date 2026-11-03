<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMedicalAidSocietiesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('medical_aid_societies')) {
            Schema::create('medical_aid_societies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->nullable();
                $table->string('logo')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });

            // Seed default top Zimbabwe Medical Aid Societies
            DB::table('medical_aid_societies')->insert([
                ['name' => 'CIMAS Medical Aid Society', 'code' => 'CIMAS', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'PSMAS (Premier Service Medical Aid)', 'code' => 'PSMAS', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'First Mutual Health', 'code' => 'FMH', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Generation Health Medical Scheme', 'code' => 'GENHEALTH', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Alliance Health', 'code' => 'ALLIANCE', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'CellMed Health Medical Scheme', 'code' => 'CELLMED', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Heritage Health Fund', 'code' => 'HERITAGE', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Liberty Health Cover', 'code' => 'LIBERTY', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Fidelity Life Medical Aid', 'code' => 'FIDELITY', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('doctor_medical_aid')) {
            Schema::create('doctor_medical_aid', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doctor_id');
                $table->unsignedBigInteger('medical_aid_society_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pharmacy_medical_aid')) {
            Schema::create('pharmacy_medical_aid', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pharmacy_id');
                $table->unsignedBigInteger('medical_aid_society_id');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('book_appointment')) {
            Schema::table('book_appointment', function (Blueprint $table) {
                if (!Schema::hasColumn('book_appointment', 'medical_aid_society_id')) {
                    $table->unsignedBigInteger('medical_aid_society_id')->nullable()->after('payment_type');
                    $table->string('medical_aid_name')->nullable()->after('medical_aid_society_id');
                    $table->string('medical_aid_number')->nullable()->after('medical_aid_name');
                    $table->string('medical_aid_suffix')->nullable()->after('medical_aid_number');
                    $table->string('medical_aid_scheme')->nullable()->after('medical_aid_suffix');
                    $table->string('medical_aid_member_name')->nullable()->after('medical_aid_scheme');
                    $table->string('medical_aid_card_photo')->nullable()->after('medical_aid_member_name');
                    $table->string('medical_aid_status')->default('pending_verification')->nullable()->after('medical_aid_card_photo');
                }
            });
        }

        if (Schema::hasTable('pharmacy_order')) {
            Schema::table('pharmacy_order', function (Blueprint $table) {
                if (!Schema::hasColumn('pharmacy_order', 'medical_aid_society_id')) {
                    $table->unsignedBigInteger('medical_aid_society_id')->nullable()->after('payment_type');
                    $table->string('medical_aid_name')->nullable()->after('medical_aid_society_id');
                    $table->string('medical_aid_number')->nullable()->after('medical_aid_name');
                    $table->string('medical_aid_suffix')->nullable()->after('medical_aid_number');
                    $table->string('medical_aid_scheme')->nullable()->after('medical_aid_suffix');
                    $table->string('medical_aid_member_name')->nullable()->after('medical_aid_scheme');
                    $table->string('medical_aid_card_photo')->nullable()->after('medical_aid_member_name');
                    $table->string('medical_aid_status')->default('pending_verification')->nullable()->after('medical_aid_card_photo');
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
        Schema::dropIfExists('medical_aid_societies');
        Schema::dropIfExists('doctor_medical_aid');
        Schema::dropIfExists('pharmacy_medical_aid');

        if (Schema::hasTable('book_appointment')) {
            Schema::table('book_appointment', function (Blueprint $table) {
                $table->dropColumn([
                    'medical_aid_society_id',
                    'medical_aid_name',
                    'medical_aid_number',
                    'medical_aid_suffix',
                    'medical_aid_scheme',
                    'medical_aid_member_name',
                    'medical_aid_card_photo',
                    'medical_aid_status'
                ]);
            });
        }

        if (Schema::hasTable('pharmacy_order')) {
            Schema::table('pharmacy_order', function (Blueprint $table) {
                $table->dropColumn([
                    'medical_aid_society_id',
                    'medical_aid_name',
                    'medical_aid_number',
                    'medical_aid_suffix',
                    'medical_aid_scheme',
                    'medical_aid_member_name',
                    'medical_aid_card_photo',
                    'medical_aid_status'
                ]);
            });
        }
    }
}
