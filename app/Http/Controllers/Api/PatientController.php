<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Requests\StorePatientRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    /** List/search patients; doctors receive only patients assigned to them. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Patient::query();

        if ($user->role === 'doctor') {
            // Normalize an optional "Dr." prefix before matching appointment data.
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
            // Search works with either a patient name or the generated PHN.
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
    /** Validate and register a patient while recording which staff member created it. */
    public function store(StorePatientRequest $request)
    {
        $this->authorize('create', Patient::class);

        $patient = DB::transaction(fn() => Patient::create(
            $request->validated() + ['created_by' => $request->user()->id]
        ));

        return response()->json($patient, 201);
    }
    /** Return a complete patient profile with visits, cases, records, and creator. */
    public function show(Request $request, Patient $patient)
    {
        $user = $request->user();

        if ($user->role === 'doctor') {
            // Record-level check: doctors cannot open an unassigned patient's profile.
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

    /** Update patient demographics while preventing duplicate identities . */
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

        #check if the patient already exists with the same full name and date of birth
        // Exclude the current patient while checking name + birth-date uniqueness.
        $normalizedName = strtolower(trim($validated['full_name']));
        $duplicateIdentity = Patient::query()
            ->whereKeyNot($patient->id)
            ->whereRaw('LOWER(TRIM(full_name)) = ?', [$normalizedName])
            ->whereDate('date_of_birth', $validated['date_of_birth'])
            ->exists();

        if ($duplicateIdentity) {
            throw ValidationException::withMessages([
                'full_name' => ['A patient with the same full name and date of birth already exists.'],
            ]);
        }

        // A non-empty phone number may belong to only one patient.
        if (! empty($validated['phone'])) {
            $duplicatePhone = Patient::query()
                ->whereKeyNot($patient->id)
                ->where('phone', trim($validated['phone']))
                ->exists();

            if ($duplicatePhone) {
                throw ValidationException::withMessages([
                    'phone' => ['This phone number is already assigned to another patient.'],
                ]);
            }
        }

        $patient->update($validated);

        return response()->json($patient);
    }

    /** Delete only patients with no dependent clinical history. */
    public function destroy(Patient $patient)
    {
        #check if the patient has any related records (appointments, cases, or medical records)
        $hasRelatedRecords =
            $patient->appointments()->exists() ||
            $patient->cases()->exists() ||
            $patient->medicalRecords()->exists();

        // Preserve referential and clinical history instead of cascading a user action.
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
