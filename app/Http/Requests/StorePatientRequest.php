<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role middleware and PatientPolicy handle authorization separately.
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'     => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender'        => ['nullable', 'string', 'max:50'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'address'       => ['nullable', 'string', 'max:500'],
            'blood_type'    => ['nullable', 'string', 'max:10'],
        ];
    }

    /** Add cross-field duplicate checks after the basic field rules pass. */
    public function withValidator(Validator $validator): void
    {

        #check if the patient already exists with the same full name and date of birth
        $validator->after(function (Validator $validator) {
            // A matching normalized name and birth date indicates the same identity.
            if ($this->filled('full_name') && $this->filled('date_of_birth')) {
                $normalizedName = strtolower(trim($this->string('full_name')->toString()));

                $duplicateIdentity = Patient::query()
                    ->whereRaw('LOWER(TRIM(full_name)) = ?', [$normalizedName])
                    ->whereDate('date_of_birth', $this->input('date_of_birth'))
                    ->exists();

                if ($duplicateIdentity) {
                    $validator->errors()->add(
                        'full_name',
                        'A patient with the same full name and date of birth already exists.'
                    );
                }
            }

            // A phone number may be assigned to only one patient.
            if ($this->filled('phone')) {
                $duplicatePhone = Patient::query()
                    ->where('phone', trim($this->string('phone')->toString()))
                    ->exists();

                if ($duplicatePhone) {
                    $validator->errors()->add(
                        'phone',
                        'This phone number is already assigned to another patient.'
                    );
                }
            }
        });
    }
}
