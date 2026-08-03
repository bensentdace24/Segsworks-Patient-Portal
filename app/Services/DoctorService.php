<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DoctorService
{
    /** Fetch and normalize the external Segworks doctor directory. */
    public function getAll(): array
    {
        // Cache for 15 minutes to reduce latency and external API traffic.
        return Cache::remember('segworks_doctors', now()->addMinutes(15), function () {
            $response = Http::withoutVerifying()
                ->withBasicAuth(
                    config('services.segworks_doctor_api.user'),
                    config('services.segworks_doctor_api.pass')
                )
                ->get('https://18.141.212.73/segservice/doctor/show/');

            // Return an empty directory instead of crashing when the service is down.
            if (! $response->successful()) {
                return [];
            }

            // Convert vendor-specific fields into the simple JSON shape Vue expects.
            return collect($response->json())->map(function ($doctor) {
                return [
                    'id' => $doctor['personnel_nr'],
                    'name' => trim("{$doctor['name_first']} {$doctor['name_last']}"),
                    'specialty' => $doctor['name_formal'],
                    'department_code' => $doctor['deptid'],
                    'photo_url' => null,
                ];
            })->toArray();
        });
    }
}
