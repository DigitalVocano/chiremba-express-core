<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Patient;
use App\Models\Doctors;
use Illuminate\Support\Facades\Hash;

class HashExistingPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hash-existing-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash all existing plain-text passwords for Patients and Doctors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to hash existing passwords...');

        // Hashing Patient Passwords
        $this->info('Processing Patients...');
        $patients = Patient::all();
        $patientCount = 0;
        foreach ($patients as $patient) {
            if (isset($patient->password) && !Hash::needsRehash($patient->password)) {
                $patient->password = $patient->password;
                $patient->save();
                $patientCount++;
            }
        }
        $this->info("Hashed passwords for {$patientCount} patients.");

        // Hashing Doctor Passwords
        $this->info('Processing Doctors...');
        $doctors = Doctors::all();
        $doctorCount = 0;
        foreach ($doctors as $doctor) {
            if (isset($doctor->password) && !Hash::needsRehash($doctor->password)) {
                $doctor->password = $doctor->password;
                $doctor->save();
                $doctorCount++;
            }
        }
        $this->info("Hashed passwords for {$doctorCount} doctors.");

        $this->info('Password hashing complete!');
        return 0;
    }
}
