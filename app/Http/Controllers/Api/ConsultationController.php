<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreConsultationRequest;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function store(StoreConsultationRequest $request, Appointment $appointment)
    {
        $user = $request->user();

        $doctorName = preg_replace(
            '/^Dr\.?\s+/i',
            '',
            trim($user->name)
        );

        $assignedDoctor = strtolower(trim((string) $appointment->doctor_name));

        abort_unless(
            $user->role === 'doctor'
                && $assignedDoctor === strtolower($doctorName),
            403,
            'This appointment is not assigned to you.'
        );


        DB::transaction(function () use ($request, $appointment) {

            $appointment->update([
                'status' => 'completed',
            ]);

            $patientCase = $appointment->patientCase;

            if (! $patientCase) {
                $patientCase = $appointment->patientCase()->create([
                    'patient_id' => $appointment->patient_id,
                    'opened_by' => $request->user()->id,
                    'status' => 'open',
                ]);
            }
            $patientCase->update([
                'diagnosis' => $request->diagnosis,
                'consultation_notes' => $request->consultation_notes,
                'prescription' => $request->prescription,
                'treatment_plan' => $request->treatment_plan,
                'follow_up_instructions' => $request->follow_up_instructions,
                'status' => 'closed',
                'completed_by' => $request->user()->id,
            ]);

            MedicalRecord::create([
                'patient_id' => $appointment->patient_id,
                'record_type' => 'Consultation',
                'title' => 'Consultation Result',
                'summary' =>
                "Diagnosis: {$request->diagnosis}\n\n" .
                    "Consultation Notes: {$request->consultation_notes}\n\n" .
                    "Prescription: {$request->prescription}\n\n" .
                    "Treatment Plan: {$request->treatment_plan}\n\n" .
                    "Follow-up: {$request->follow_up_instructions}",
                'recorded_at' => now(),
            ]);
            return response()->json([
                'message' => 'Consultation completed successfully.',
                'appointment' => $appointment->fresh()->load('patientCase'),
            ]);
        });
    }
}
