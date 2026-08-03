<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // AppointmentPolicy and route middleware handle authorization separately.
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_name' => ['nullable', 'string'],
            'department'   => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'status'       => ['required', 'in:pending,confirmed,completed,cancelled'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    /** Prevent an edited appointment from overlapping another appointment. */
    public function withValidator(Validator $validator): void
    {
        if (! $this->filled('doctor_id') || ! $this->filled('scheduled_at')) {
            return;
        }

        /** @var Appointment $appointment */
        $appointment = $this->route('appointment');

        $doctorId = $this->input('doctor_id');
        $scheduledAt = Carbon::parse($this->input('scheduled_at'));

        $conflict = Appointment::where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled'])
            ->where('id', '!=', $appointment?->id)
            ->whereBetween('scheduled_at', [
                $scheduledAt->copy()->subMinutes(29),
                $scheduledAt->copy()->addMinutes(29),
            ])
            ->exists();

        if ($conflict) {
            $validator->errors()->add(
                'scheduled_at',
                'This doctor already has an appointment at that time.'
            );
        }
    }
}
