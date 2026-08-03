<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{
    /** List appointments; doctors are restricted to appointments assigned to them. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Appointment::query()->with('patient');

        if ($user->role === 'doctor') {
            $doctorName = preg_replace(
                '/^Dr\.?\s+/i',
                '',
                trim($user->name)
            );

            $query->whereRaw(
                'LOWER(TRIM(COALESCE(doctor_name, ""))) = ?',
                [strtolower($doctorName)]
            );
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        return response()->json(
            $query->orderByDesc('scheduled_at')->get()
        );
    }
    /** Create an appointment and its initial patient case as one atomic operation. */
    public function store(StoreAppointmentRequest $request)
    {
        $this->authorize('create', Appointment::class);

        // A transaction prevents an appointment from existing without its case.
        $appointment = DB::transaction(function () use ($request) {
            $appointment = Appointment::create($request->validated() + [
                'status' => 'pending',
                'created_by' => $request->user()->id,
            ]);

            $appointment->patientCase()->create([
                'patient_id' => $appointment->patient_id,
                'opened_by' => $request->user()->id,
            ]);

            return $appointment;
        });

        return response()->json(
            $appointment->load(['patient', 'patientCase']),
            201
        );
    }

    /** Return one appointment together with its patient and clinical case. */
    public function show(Appointment $appointment)
    {
        return response()->json(
            $appointment->load(['patient', 'patientCase'])
        );
    }
    /** Validate and update appointment details or status. */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return response()->json($appointment->load(['patient', 'doctor']));
    }
    /** Permanently delete an appointment after route-level role authorization. */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}
