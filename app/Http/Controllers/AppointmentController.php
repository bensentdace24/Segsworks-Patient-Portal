<?php

namespace App\Http\Controllers;


use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = $request->user()->patient->appointments()->latest('scheduled_at')->get();

        if ($request->ajax()) {
            return view('appointment._list', compact('appointments'));
        }

        return view('appointment.index', compact('appointments'));
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
            'doctor_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $request->user()->patient->appointments()->create($validated + ['status' => 'pending']);

        return redirect()->route('appointments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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

            'doctor_name'   => 'required|string|max:255',
            'department'    => 'nullable|string|max:255',
            'scheduled_at'  => 'required|date',
            'status'        => 'required|in:pending,confirmed,completed,cancelled',
            'notes'         => 'nullable|string',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
