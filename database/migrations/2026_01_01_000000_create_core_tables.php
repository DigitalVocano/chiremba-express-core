<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoreTables extends Migration
{
    /**
     * Create base tables if they do not exist so fresh open-source installations
     * and automated test suites can run `php artisan migrate --seed` cleanly.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('setting')) {
            Schema::create('setting', function (Blueprint $table) {
                $table->id();
                $table->string('title')->default('Chiremba Express');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('logo')->default('logo.png');
                $table->string('favicon')->default('favicon.ico');
                $table->string('currency')->default('USD-$');
                $table->string('lang_code')->default('en');
                $table->tinyInteger('is_rtl')->default(0);
                $table->tinyInteger('is_wallet_enabled')->default(1);
                $table->decimal('commission', 8, 2)->default(10.00);
                $table->decimal('pharmacy_tax', 8, 2)->default(0.00);
                $table->decimal('pharmacy_delivery_charge', 8, 2)->default(2.00);
                $table->integer('ai_free_daily_limit')->default(5);
                $table->string('google_tag_manager_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('profile_pic')->nullable();
                $table->text('permissions')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('patient')) {
            Schema::create('patient', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_demo')->default(0);
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('phone')->nullable()->index();
                $table->string('address')->nullable();
                $table->string('google_id')->nullable();
                $table->string('profile_pic')->nullable();
                $table->string('connectycube_user_id')->nullable();
                $table->string('login_id')->nullable();
                $table->string('connectycube_password')->nullable();
                $table->tinyInteger('is_patient_subscription')->default(0);
                $table->string('referral_code', 32)->nullable()->unique();
                $table->string('referred_by_code', 32)->nullable();
                $table->integer('referral_points')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->tinyInteger('recommendable')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_demo')->default(0);
                $table->string('name');
                $table->string('slug')->nullable()->index();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->string('phoneno')->nullable()->index();
                $table->text('address')->nullable();
                $table->string('lat')->nullable();
                $table->string('lon')->nullable();
                $table->string('image')->nullable();
                $table->string('working_time')->nullable();
                $table->decimal('consultation_fees', 10, 2)->default(15.00);
                $table->text('aboutus')->nullable();
                $table->text('services')->nullable();
                $table->text('healthcare')->nullable();
                $table->string('profile_type')->default('1'); // 1 = Doctor, 2 = Pharmacy
                $table->string('is_approve')->default('1');
                $table->tinyInteger('recommendable')->default(1);
                $table->string('referral_code', 32)->nullable();
                $table->string('referred_by_code', 32)->nullable();
                $table->integer('referral_points')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('medicines')) {
            Schema::create('medicines', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('dosage')->nullable();
                $table->text('description')->nullable();
                $table->string('category')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pharmacy_products')) {
            Schema::create('pharmacy_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pharmacy_id')->index();
                $table->unsignedBigInteger('medicine_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->integer('stock')->default(100);
                $table->string('image')->nullable();
                $table->string('category')->nullable();
                $table->boolean('is_srhr_supply')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('book_appointment')) {
            Schema::create('book_appointment', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('doctor_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('date')->nullable();
                $table->string('slot_id')->nullable();
                $table->string('slot_name')->nullable();
                $table->text('user_description')->nullable();
                $table->string('status')->default('1');
                $table->string('payment_type')->nullable();
                $table->decimal('consultation_fees', 10, 2)->default(0.00);
                $table->string('prescription_file')->nullable();
                $table->boolean('is_urgent_maternal_referral')->default(false);
                $table->unsignedBigInteger('recorded_by_chw_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('appointment_medicines')) {
            Schema::create('appointment_medicines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('appointment_id')->index();
                $table->unsignedBigInteger('medicine_id')->nullable();
                $table->string('medicine_name')->nullable();
                $table->string('dosage')->nullable();
                $table->string('frequency')->nullable();
                $table->string('duration')->nullable();
                $table->text('instructions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pharmacy_order')) {
            Schema::create('pharmacy_order', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('Pharmacy_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->decimal('total', 10, 2)->default(0.00);
                $table->string('payment_type')->default('Wallet');
                $table->string('is_completed')->default('1');
                $table->integer('order_type')->default(2);
                $table->integer('status')->default(0);
                $table->string('prescription')->nullable();
                $table->string('phone')->nullable();
                $table->text('message')->nullable();
                $table->text('address')->nullable();
                $table->decimal('tax', 10, 2)->default(0.00);
                $table->decimal('delivery_charge', 10, 2)->default(0.00);
                $table->string('transaction_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pharmacy_orderdata')) {
            Schema::create('pharmacy_orderdata', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->integer('qty')->default(1);
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pharmacy_order_data')) {
            Schema::create('pharmacy_order_data', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->integer('qty')->default(1);
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('health_records')) {
            Schema::create('health_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->index();
                $table->date('date');
                $table->string('blood_pressure')->nullable();
                $table->integer('blood_pressure_systolic')->nullable();
                $table->integer('blood_pressure_diastolic')->nullable();
                $table->decimal('weight', 6, 2)->nullable();
                $table->decimal('temperature', 5, 2)->nullable();
                $table->integer('blood_glucose_level')->nullable();
                $table->decimal('glucose_level', 6, 2)->nullable();
                $table->integer('heart_rate')->nullable();
                $table->integer('hrv')->nullable();
                $table->integer('blood_oxygen_saturation')->nullable();
                $table->integer('gestational_weeks')->nullable();
                $table->integer('fetal_heart_rate')->nullable();
                $table->decimal('fundal_height_cm', 5, 2)->nullable();
                $table->text('ecg_data')->nullable();
                $table->text('sleep_data')->nullable();
                $table->integer('steps')->nullable();
                $table->decimal('distance', 8, 2)->nullable();
                $table->integer('calories_burned')->nullable();
                $table->boolean('fall_detected')->default(false);
                $table->integer('stress_level')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('recorded_by_role')->default('patient'); // patient, chw, nurse, doctor
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('medical_records')) {
            Schema::create('medical_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->index();
                $table->string('document_name');
                $table->string('document_path')->nullable();
                $table->string('record_type')->default('pdf');
                $table->string('visibility')->default('public');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type');
                $table->unsignedBigInteger('owner_id');
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->integer('ai_credits')->default(5);
                $table->timestamps();
                $table->index(['owner_type', 'owner_id']);
            });
        }

        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('wallet_id')->index();
                $table->string('type', 20); // credit, debit
                $table->decimal('amount', 12, 2);
                $table->string('description')->nullable();
                $table->string('reference')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('banner')) {
            Schema::create('banner', function (Blueprint $table) {
                $table->id();
                $table->string('image');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification')) {
            Schema::create('notification', function (Blueprint $table) {
                $table->id();
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_sessions')) {
            Schema::create('chat_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->string('anonymous_session_token', 64)->nullable()->index();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chat_session_id')->index();
                $table->string('role', 20);
                $table->longText('content');
                $table->string('attachment_path')->nullable();
                $table->boolean('is_final_report')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('savetoken')) {
            Schema::create('savetoken', function (Blueprint $table) {
                $table->id();
                $table->string('token')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('doctor_id')->nullable();
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
        // Core tables are preserved on rollback to prevent accidental data loss in shared environments
    }
}

