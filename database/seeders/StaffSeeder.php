<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Dr. Ana Reyes',
            'email' => 'staff@example.com',
            'role' => 'receptionist', // was 'doctor' — this one's now front-desk
        ]);

        User::factory()->create([
            'name' => 'Dr. Marco Villanueva',
            'email' => 'doctor@example.com',
            'role' => 'doctor',
        ]);
    }
}
