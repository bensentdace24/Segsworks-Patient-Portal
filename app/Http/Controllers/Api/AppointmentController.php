<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->patient->appointments()->latest('scheduled_at')->get()
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_name'  => 'required|string|max:255',
            'department'   => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
        ]);
        $appointment = $request->user()->patient->appointments()
            ->create($validated + ['status' => 'pending']);

        return response()->json($appointment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $this->authorizeAppointment($appointment, request());

        return response()->json($appointment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment, $request);

        $validated = $request->validate([
            'doctor_name'  => 'required|string|max:255',
            'department'   => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
            'notes'        => 'nullable|string',
        ]);

        $appointment->update($validated);

        return response()->json($appointment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        $this->authorizeAppointment($appointment, $request);

        $appointment->delete();

        return response()->json(null, 204);
    }

    public function authorizeAppointment(Appointment $appointment, Request $request)
    {
        abort_unless($appointment->patient_id === $request->user()->patient->id, 403);
    }
}
