<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;



class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
        ]);
        Patient::create([
            'user_id' => $user->id,
            'full_name' => 'Juan Dela Cruz',
            'date_of_birth' => '1990-05-14',
            'gender' => 'Male',
            'phone' => '0917-000-0000',
            'address' => 'Davao City, Philippines',
            'blood_type' => 'O+',
        ]);
    }
}
