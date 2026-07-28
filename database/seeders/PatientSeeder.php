<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        Patient::create([
            'full_name' => 'Juan Dela Cruz',
            'date_of_birth' => '1990-05-14',
            'gender' => 'Male',
            'phone' => '0917-000-0000',
            'address' => 'Davao City, Philippines',
            'blood_type' => 'O+',
        ]);
    }
}
