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
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        Patient::create([
            'user_id' => $user->id,
            'full_name' => 'John Doe',
            'date_of_birth' => '1990-02-14',
            'gender' => 'Male',
            'phone' => '0907-540-5541',
            'address' => 'Davao City, Philippines',
            'blood_type' => 'O+'

        ]);
    }
}
