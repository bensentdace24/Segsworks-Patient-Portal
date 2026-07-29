<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('patient');

        if ($patientId = $request->query('patient_id')) {
            $query->where('patient_id', $patientId);
        }

        return response()->json($query->latest('scheduled_at')->get());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'   => 'required|exists:patients,id',
            'doctor_name'  => 'nullable|string|max:255',
            'department'   => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $appointment = Appointment::create($validated + ['status' => 'pending']);

        return response()->json($appointment->load('patient'), 201);
    }

    public function show(Appointment $appointment)
    {
        return response()->json($appointment->load(['patient', 'doctor']));
    }

    public function edit(Appointment $appointment)
    {
        //
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'doctor_name'  => 'nullable|string|max:255',
            'department'   => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
            'notes'        => 'nullable|string',
        ]);

        $appointment->update($validated);

        return response()->json($appointment->load('patient'));
    }
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return response()->json(null, 204);
    }
}
