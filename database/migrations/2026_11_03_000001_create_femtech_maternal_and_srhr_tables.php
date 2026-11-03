<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFemtechMaternalAndSrhrTables extends Migration
{
    /**
     * Run the migrations for the 5 UNICEF Femtech & Maternal/Child Health Pillars.
     *
     * @return void
     */
    public function up()
    {
        // Pillar 1A: Maternal Profiles & Antenatal Health Tracking
        if (!Schema::hasTable('maternal_profiles')) {
            Schema::create('maternal_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->string('anonymous_session_token', 64)->nullable()->index();
                $table->unsignedBigInteger('registered_by_chw_id')->nullable()->index();
                $table->date('lmp_date')->nullable();
                $table->date('edd_date')->nullable();
                $table->integer('gestational_weeks')->default(12);
                $table->integer('gravida')->default(1);
                $table->integer('para')->default(0);
                $table->string('blood_group', 10)->nullable();
                $table->boolean('high_risk_flag')->default(false);
                $table->text('risk_factors')->nullable(); // JSON array of clinical risk factors
                $table->string('preferred_language', 10)->default('en'); // en, sn, nr
                $table->boolean('sms_reminders_enabled')->default(true);
                $table->timestamps();
            });
        }

        // Pillar 1B: Gestational Milestone & Danger-Sign Triage Logs
        if (!Schema::hasTable('maternal_triage_logs')) {
            Schema::create('maternal_triage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->unsignedBigInteger('maternal_profile_id')->nullable()->index();
                $table->unsignedBigInteger('recorded_by_chw_id')->nullable()->index();
                $table->string('anonymous_session_token', 64)->nullable()->index();
                $table->integer('gestational_weeks');
                $table->text('symptoms'); // JSON array of reported symptoms
                $table->integer('systolic_bp')->nullable();
                $table->integer('diastolic_bp')->nullable();
                $table->decimal('temperature', 5, 2)->nullable();
                $table->decimal('weight_kg', 6, 2)->nullable();
                $table->decimal('blood_glucose_mmol', 5, 2)->nullable();
                $table->string('risk_level', 30)->default('low')->index(); // low, moderate, high, critical
                $table->text('flagged_conditions')->nullable(); // JSON array (e.g. Pre-eclampsia, Gestational Hypertension, Miscarriage Risk)
                $table->text('clinical_guidance');
                $table->boolean('urgent_referral_triggered')->default(false)->index();
                $table->unsignedBigInteger('referral_appointment_id')->nullable();
                $table->string('channel', 30)->default('web'); // web, chw_tablet, whatsapp, ussd, sms
                $table->string('locale', 10)->default('en'); // en, sn, nr
                $table->timestamps();
            });
        }

        // Pillar 1C: First 1,000 Days Child Growth & Vaccination Tracker
        if (!Schema::hasTable('child_profiles')) {
            Schema::create('child_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mother_patient_id')->nullable()->index();
                $table->unsignedBigInteger('registered_by_chw_id')->nullable()->index();
                $table->string('child_name');
                $table->date('date_of_birth');
                $table->string('sex', 15)->nullable();
                $table->decimal('birth_weight_kg', 5, 2)->nullable();
                $table->decimal('birth_length_cm', 5, 2)->nullable();
                $table->string('feeding_mode', 40)->default('exclusive_breastfeeding');
                $table->string('guardian_phone', 30)->nullable();
                $table->string('preferred_language', 10)->default('en');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('child_vaccine_milestones')) {
            Schema::create('child_vaccine_milestones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('child_profile_id')->index();
                $table->string('vaccine_code', 40); // BCG, OPV_0, PENTA_1, PCV_1, ROTA_1, MEASLES_1, etc.
                $table->string('vaccine_name');
                $table->integer('target_age_weeks')->default(0);
                $table->date('due_date');
                $table->date('administered_date')->nullable();
                $table->string('status', 20)->default('upcoming')->index(); // upcoming, due, completed, overdue
                $table->decimal('weight_kg', 5, 2)->nullable();
                $table->decimal('height_cm', 5, 2)->nullable();
                $table->decimal('muac_cm', 5, 2)->nullable(); // Mid-Upper Arm Circumference for nutritional screening
                $table->text('nutrition_checkin_notes')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();
                $table->timestamps();
            });
        }

        // Pillar 1D: Ensure Maternal Vitals columns exist on health_records
        if (Schema::hasTable('health_records')) {
            Schema::table('health_records', function (Blueprint $table) {
                if (!Schema::hasColumn('health_records', 'blood_pressure_systolic')) {
                    $table->integer('blood_pressure_systolic')->nullable();
                }
                if (!Schema::hasColumn('health_records', 'blood_pressure_diastolic')) {
                    $table->integer('blood_pressure_diastolic')->nullable();
                }
                if (!Schema::hasColumn('health_records', 'gestational_weeks')) {
                    $table->integer('gestational_weeks')->nullable();
                }
                if (!Schema::hasColumn('health_records', 'fetal_heart_rate')) {
                    $table->integer('fetal_heart_rate')->nullable();
                }
                if (!Schema::hasColumn('health_records', 'fundal_height_cm')) {
                    $table->decimal('fundal_height_cm', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('health_records', 'recorded_by_role')) {
                    $table->string('recorded_by_role', 30)->default('patient');
                }
            });
        }

        // Pillar 2: Confidential Sexual & Reproductive Health (SRHR) Portal & Discreet Courier Dispatch
        if (!Schema::hasTable('srhr_anonymous_sessions')) {
            Schema::create('srhr_anonymous_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_token', 64)->unique();
                $table->string('alias_code', 24)->index();
                $table->string('age_bracket', 20)->nullable();
                $table->string('preferred_language', 10)->default('en');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('srhr_inquiries')) {
            Schema::create('srhr_inquiries', function (Blueprint $table) {
                $table->id();
                $table->string('anonymous_session_token', 64)->nullable()->index();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->string('topic_category', 50)->index(); // contraception, emergency_contraception, menstrual_health, sti_testing, fertility
                $table->text('question');
                $table->longText('guidance_response');
                $table->string('who_guideline_ref')->nullable();
                $table->text('recommended_supplies')->nullable(); // JSON array
                $table->string('locale', 10)->default('en');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('pharmacy_order')) {
            Schema::table('pharmacy_order', function (Blueprint $table) {
                if (!Schema::hasColumn('pharmacy_order', 'is_discreet_packaging')) {
                    $table->boolean('is_discreet_packaging')->default(false);
                }
                if (!Schema::hasColumn('pharmacy_order', 'discreet_verification_pin')) {
                    $table->string('discreet_verification_pin', 10)->nullable();
                }
                if (!Schema::hasColumn('pharmacy_order', 'anonymous_session_token')) {
                    $table->string('anonymous_session_token', 64)->nullable()->index();
                }
                if (!Schema::hasColumn('pharmacy_order', 'srhr_category')) {
                    $table->string('srhr_category', 50)->nullable();
                }
            });
        }

        // Pillar 3: Frontline Community Health Worker (CHW) Proxy Intake & Offline Sync
        if (!Schema::hasTable('chw_proxy_intakes')) {
            Schema::create('chw_proxy_intakes', function (Blueprint $table) {
                $table->id();
                $table->string('offline_uuid', 64)->unique(); // Guarantees idempotency when syncing offline records
                $table->unsignedBigInteger('chw_user_id')->nullable()->index();
                $table->string('chw_name');
                $table->string('chw_location_ward')->nullable();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->string('patient_full_name');
                $table->string('patient_phone', 30)->nullable();
                $table->integer('patient_age')->nullable();
                $table->boolean('is_pregnant')->default(false);
                $table->integer('gestational_weeks')->nullable();
                $table->text('vital_signs')->nullable(); // JSON: systolic_bp, diastolic_bp, weight_kg, glucose_mmol, temperature, muac_cm
                $table->text('symptoms')->nullable(); // JSON array
                $table->string('triage_risk_level', 30)->default('low')->index();
                $table->text('triage_summary')->nullable();
                $table->unsignedBigInteger('appointment_id')->nullable();
                $table->boolean('synced_from_offline')->default(false);
                $table->timestamp('client_recorded_at')->nullable();
                $table->timestamps();
            });
        }

        // Pillar 4: Maternal Mental Health Screening (EPDS - Edinburgh Postnatal Depression Scale)
        if (!Schema::hasTable('epds_screenings')) {
            Schema::create('epds_screenings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->nullable()->index();
                $table->string('anonymous_session_token', 64)->nullable()->index();
                $table->unsignedBigInteger('recorded_by_chw_id')->nullable()->index();
                $table->string('perinatal_stage', 40)->default('postpartum'); // antenatal, postpartum_0_6w, postpartum_6w_12m
                $table->text('responses'); // JSON array of 10 question scores (0-3)
                $table->integer('total_score'); // 0 to 30
                $table->integer('self_harm_item_score')->default(0); // Question 10 score (0-3)
                $table->string('risk_severity', 30)->index(); // low, moderate, high_distress, urgent_crisis
                $table->boolean('counselor_referral_surfaced')->default(false);
                $table->text('support_hotlines')->nullable(); // JSON array of surfaced hotlines/counselors
                $table->string('locale', 10)->default('en');
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
        Schema::dropIfExists('epds_screenings');
        Schema::dropIfExists('chw_proxy_intakes');
        Schema::dropIfExists('srhr_inquiries');
        Schema::dropIfExists('srhr_anonymous_sessions');
        Schema::dropIfExists('child_vaccine_milestones');
        Schema::dropIfExists('child_profiles');
        Schema::dropIfExists('maternal_triage_logs');
        Schema::dropIfExists('maternal_profiles');

        if (Schema::hasTable('pharmacy_order')) {
            Schema::table('pharmacy_order', function (Blueprint $table) {
                $cols = [];
                foreach (['is_discreet_packaging', 'discreet_verification_pin', 'anonymous_session_token', 'srhr_category'] as $col) {
                    if (Schema::hasColumn('pharmacy_order', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
}

