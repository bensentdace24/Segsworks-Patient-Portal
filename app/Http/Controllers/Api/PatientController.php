<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Requests\StorePatientRequest;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Patient::query();

        if ($user->role === 'doctor') {
            $doctorName = preg_replace(
                '/^Dr\.?\s+/i',
                '',
                trim($user->name)
            );

            $query->whereHas('appointments', function ($appointmentQuery) use ($doctorName) {
                $appointmentQuery->whereRaw(
                    'LOWER(TRIM(doctor_name)) = ?',
                    [strtolower($doctorName)]
                );
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phn', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->latest()->get()
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
    public function store(StorePatientRequest $request)
    {
        $this->authorize('create', Patient::class);

        $patient = Patient::create($request->validated() + ['created_by' => $request->user()->id]);

        return response()->json($patient, 201);
    }
    /**
     * Display the specified resource.
     */
    // PatientController@show — add eager loading
    public function show(Request $request, Patient $patient)
    {
        $user = $request->user();

        if ($user->role === 'doctor') {
            $doctorName = preg_replace(
                '/^Dr\.?\s+/i',
                '',
                trim($user->name)
            );

            $isAssigned = $patient->appointments()
                ->whereRaw(
                    'LOWER(TRIM(COALESCE(doctor_name, ""))) = ?',
                    [strtolower($doctorName)]
                )
                ->exists();

            abort_unless(
                $isAssigned,
                403,
                'You are not authorized to view this patient.'
            );
        }

        return response()->json(
            $patient->load([
                'appointments.patientCase',
                'medicalRecords',
                'createdBy:id,name',
            ])
        );
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
        $hasRelatedRecords =
            $patient->appointments()->exists() ||
            $patient->cases()->exists() ||
            $patient->medicalRecords()->exists();

        if ($hasRelatedRecords) {
            return response()->json([
                'message' => 'This patient cannot be deleted because they already have appointments, consultations, or medical records.',
            ], 422);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient deleted successfully.',
        ]);
    }
}
