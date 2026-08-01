<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;


class AppointmentController extends Controller
{
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
    public function create()
    {
        //
    }

    public function store(StoreAppointmentRequest $request)
    {
        $this->authorize('create', Appointment::class);

        $appointment = Appointment::create($request->validated() + [
            'status' => 'pending',
            'created_by' => $request->user()->id, // audit trail: which nurse booked it
        ]);

        #tthis needs created_by added to appointment to add enew column
        $appointment->patientCase()->create([
            'patient_id' => $appointment->patient_id,
            'opened_by' => $request->user()->id,
        ]);
    }

    public function show(Appointment $appointment)
    {
        return response()->json(
            $appointment->load(['patient', 'patientCase'])
        );
    }
    public function edit(Appointment $appointment)
    {
        //
    }


    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validated());

        return response()->json($appointment->load(['patient', 'doctor']));
    }
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}
