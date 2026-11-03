<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Doctors;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Seed demo accounts for Patient, Doctor, and Pharmacy.
     *
     * Demo Numbers:
     * - Patient: 0771234567
     * - Doctor: 0771234568
     * - Pharmacy: 0771234569
     *
     * Fixed OTP: 123456
     */
    public function run()
    {
        // 1. Patient Demo (0771234567)
        Patient::updateOrCreate(
            ['phone' => '0771234567'],
            [
                'name' => 'Demo Patient',
                'email' => 'patientdemo@chiremba.com',
                'password' => Hash::make('123456'),
                'is_demo' => 1
            ]
        );

        // 2. Doctor Demo (0771234568)
        Doctors::updateOrCreate(
            ['phoneno' => '0771234568'],
            [
                'name' => 'Dr. Demo Doctor',
                'email' => 'doctordemo@chiremba.com',
                'password' => Hash::make('123456'),
                'profile_type' => '1', // Doctor
                'is_approve' => '1',
                'is_demo' => 1,
                'address' => 'Harare Central Hospital, Zimbabwe',
                'department_id' => 1
            ]
        );

        // 3. Pharmacy Demo (0771234569)
        Doctors::updateOrCreate(
            ['phoneno' => '0771234569'],
            [
                'name' => 'Demo Pharmacy',
                'email' => 'pharmacydemo@chiremba.com',
                'password' => Hash::make('123456'),
                'profile_type' => '2', // Pharmacy
                'is_approve' => '1',
                'is_demo' => 1,
                'address' => 'Main Street Pharmacy, Harare, Zimbabwe',
                'department_id' => 1
            ]
        );
    }
}
