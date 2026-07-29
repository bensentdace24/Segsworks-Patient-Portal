<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($search = $request->query('search')) {
            $query->where('full_name', 'like', "%{$search}%")
                ->orWhere('phn', 'like', "%{$search}%");
        }

        return response()->json($query->latest()->get());
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
            'full_name'     => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender'        => 'nullable|string',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
            'blood_type'    => 'nullable|string',
        ]);

        $patient = Patient::create($validated + ['created_by' => $request->user()->id]);


        return response()->json($patient, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return response()->json($patient->load(['appointments', 'medicalRecords']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender'        => 'nullable|string',
            'phone'         => 'nullable|string',
            'address'       => 'nullable|string',
            'blood_type'    => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json($patient);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }
}
