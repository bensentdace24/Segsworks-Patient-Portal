<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patient = Patient::first(); // Get the first patient from the database

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Smith',
            'department' => 'Cardiology',
            'scheduled_at' => now()->addDays(3), // Schedule for 3 days from now
            'status' => 'confirmed',
            'notes' => 'Routine checkup.'
        ]);

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_name' => 'Dr. Johnson',
            'department' => 'Dertamtology',
            'scheduled_at' => now()->subDays(2), // Schedule for 2 days
            'status' => 'completed',
            'notes' => 'Follow-up on lab results.'
        ]);
    }
}
