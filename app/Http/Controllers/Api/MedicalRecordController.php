<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    /** List medical records; doctors receive only records for assigned patients. */
    public function index(Request $request)
    {
        #check if the user is a doctor and filter records accordingly
        $query = MedicalRecord::with('patient');

        if ($request->user()->role === 'doctor') {
            // Filter at the database level so unauthorized records never reach Vue.
            $doctorName = $this->normalizedDoctorName($request->user()->name);

            $query->whereHas('patient.appointments', function ($appointmentQuery) use ($doctorName) {
                $appointmentQuery->whereRaw(
                    'LOWER(TRIM(COALESCE(doctor_name, ""))) = ?',
                    [$doctorName]
                );
            });
        }

        return response()->json($query->latest('recorded_at')->get());
    }

    /** Create a diagnosis, laboratory result, or other manual clinical record. */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'record_type' => ['required', 'in:Diagnosis,Lab Result,Other'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'recorded_at' => ['required', 'date', 'before_or_equal:now'],
        ]);
        # check if the user is a doctor and if the patient is assigned to them
        if ($request->user()->role === 'doctor') {
            // Doctors may add records only for patients assigned to them.
            $doctorName = $this->normalizedDoctorName($request->user()->name);
            $patient = \App\Models\Patient::findOrFail($validated['patient_id']);

            $isAssigned = $patient->appointments()
                ->whereRaw(
                    'LOWER(TRIM(COALESCE(doctor_name, ""))) = ?',
                    [$doctorName]
                )
                ->exists();

            abort_unless(
                $isAssigned,
                403,
                'You can only add records for patients assigned to you.'
            );
        }

        $medicalRecord = MedicalRecord::create($validated);

        return response()->json($medicalRecord->load('patient'), 201);
    }

    /** Return one record after enforcing doctor-to-patient assignment. */
    public function show(Request $request, MedicalRecord $medicalRecord)
    {
        # check if the user is a doctor and if the patient is assigned to them
        $medicalRecord->load('patient');

        if ($request->user()->role === 'doctor') {
            $doctorName = $this->normalizedDoctorName($request->user()->name);

            $isAssigned = $medicalRecord->patient
                ?->appointments()
                ->whereRaw(
                    'LOWER(TRIM(COALESCE(doctor_name, ""))) = ?',
                    [$doctorName]
                )
                ->exists();

            abort_unless(
                $isAssigned,
                403,
                'You are not authorized to view this medical record.'
            );
        }

        return response()->json($medicalRecord);
    }

    /** Remove an optional "Dr." prefix and normalize the name for safe comparisons. */
    private function normalizedDoctorName(string $name): string
    {
        return strtolower(preg_replace('/^Dr\.?\s+/i', '', trim($name)));
    }
}
