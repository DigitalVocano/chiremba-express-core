<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedDemoAccountsCommand extends Command
{
    protected $signature = 'demo:setup';
    protected $description = 'Set up and complete demo accounts (0771234567, 0771234568, 0771234569) and top up wallet balances';

    public function handle()
    {
        $this->info("Setting up clean demo accounts...");

        // 1. Patient Demo (0771234567)
        $patient = Patient::updateOrCreate(
            ['phone' => '0771234567'],
            [
                'name' => 'Tariro Chiremba',
                'email' => 'patient@chirembaexpress.co.zw',
                'password' => Hash::make('12345678'),
                'profile_pic' => 'default.png',
            ]
        );

        $wallet = $patient->wallet()->firstOrCreate([]);
        $wallet->balance = 1000.00;
        $wallet->ai_credits = 50;
        $wallet->save();

        $this->info("Patient Demo set up: Phone 0771234567 | Balance: \$1000.00 | AI Credits: 50");

        // 2. Doctor Demo (0771234568)
        $doctor = Doctors::updateOrCreate(
            ['phoneno' => '0771234568'],
            [
                'name' => 'Dr. Kunashe Moyo',
                'email' => 'doctor@chirembaexpress.co.zw',
                'password' => Hash::make('12345678'),
                'profile_type' => '1', // Doctor
                'department_id' => 1,
                'aboutus' => 'Dr. Kunashe Moyo is a senior consultant physician with over 12 years of clinical experience in General Healthcare, Cardiology, and Telemedicine. Graduate of UZ College of Health Sciences.',
                'services' => 'General Consultations, ECG Analysis, Chronic Disease Management, Health Screening',
                'healthcare' => 'Chiremba Medical Centre, Avondale, Harare',
                'address' => 'King George Road, Avondale, Harare, Zimbabwe',
                'lat' => '-17.7942',
                'lon' => '31.0422',
                'consultation_fees' => '25.00',
                'working_time' => 'Mon - Sat (08:00 AM - 05:00 PM)',
                'image' => 'default.png',
                'is_approve' => '1',
                'slug' => 'dr-kunashe-moyo',
            ]
        );
        $this->info("Doctor Demo set up: Dr. Kunashe Moyo (Phone 0771234568)");

        // 3. Pharmacy Demo (0771234569)
        $pharmacy = Doctors::updateOrCreate(
            ['phoneno' => '0771234569'],
            [
                'name' => 'Chiremba Express Central Pharmacy',
                'email' => 'pharmacy@chirembaexpress.co.zw',
                'password' => Hash::make('12345678'),
                'profile_type' => '2', // Pharmacy
                'department_id' => 1,
                'aboutus' => 'Fully licensed retail & hospital pharmacy providing genuine prescription medicines, OTC supplies, wellness products, and door-to-door express delivery in Harare.',
                'services' => 'Prescription Dispensing, OTC Medicines, Express Delivery, Health Supplements',
                'healthcare' => 'Chiremba Central Hub',
                'address' => 'Second Street Extension, Mount Pleasant, Harare, Zimbabwe',
                'lat' => '-17.7831',
                'lon' => '31.0528',
                'consultation_fees' => '0.00',
                'working_time' => 'Mon - Sun (24/7 Service)',
                'image' => 'defaultpharmacy.png',
                'is_approve' => '1',
                'delivery_mode' => 'pointlink',
                'slug' => 'chiremba-central-pharmacy',
            ]
        );
        $this->info("Pharmacy Demo set up: Chiremba Express Central Pharmacy (Phone 0771234569)");

        $this->info("Demo accounts setup completed successfully!");
        return 0;
    }
}
