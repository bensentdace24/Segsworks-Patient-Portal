<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;
use App\Models\Patient;



class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patient = Patient::first(); // Get the first patient from the database

        MedicalRecord::create([
            'patient_id' => $patient->id,
            'record_type' => 'Lab Result',
            'title' => 'Complete Blood Count',
            'summary' => 'All values within normal range.',
            'recorded_at' => now()->subDays(5), // Recorded 5 days ago

        ]);

        MedicalRecord::create([
            'patient_id' => $patient->id,
            'record_type' => 'Diagnosis',
            'title' => 'Seasonal Allergies',
            'summary' => 'Prescribed antihistamine, advised follow-up if symptoms persist.',
            'recorded_at' => now()->submonths(2), // Recorded 2 months ago
        ]);
    }
}
