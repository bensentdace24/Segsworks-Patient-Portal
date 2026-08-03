<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route middleware and AppointmentPolicy decide which roles may create.
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'   => ['required', 'exists:patients,id'],
            'doctor_name' => [
                'required',
                'string',
                'max:255',
            ],
            'department'   => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after_or_equal:now'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    /** Add scheduling conflict and doctor-availability checks after field validation. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('doctor_id') || ! $this->filled('scheduled_at')) {
                return;
            }

            $doctorId = $this->input('doctor_id');
            $scheduledAt = Carbon::parse($this->input('scheduled_at'));

            // Keep a 30-minute buffer around another non-cancelled appointment.
            if ($this->doctorHasConflict($doctorId, $scheduledAt)) {
                $validator->errors()->add('scheduled_at', 'This doctor already has an appointment at that time.');
            }

            // Reject leave periods and times outside configured working hours.
            if (! $this->doctorIsAvailable($doctorId, $scheduledAt)) {
                $validator->errors()->add('scheduled_at', 'This time falls outside the doctor\'s scheduled availability.');
            }
        });
    }

    /** Determine whether the doctor already has a nearby appointment. */
    protected function doctorHasConflict(int $doctorId, Carbon $scheduledAt): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('scheduled_at', [
                $scheduledAt->copy()->subMinutes(29),
                $scheduledAt->copy()->addMinutes(29),
            ])
            ->exists();
    }

    /** Check time off and optional recurring schedules for the selected doctor. */
    protected function doctorIsAvailable(int $doctorId, Carbon $scheduledAt): bool
    {
        $doctor = \App\Models\User::find($doctorId);

        if (! $doctor) {
            return true;
        }

        $onLeave = $doctor->timeOff()
            ->where('starts_at', '<=', $scheduledAt)
            ->where('ends_at', '>=', $scheduledAt)
            ->exists();

        if ($onLeave) {
            return false;
        }

        $schedules = $doctor->schedules()->where('is_active', true)->get();

        if ($schedules->isEmpty()) {
            return true;
        }

        return $schedules->contains(fn($schedule) => $schedule->coversDateTime($scheduledAt));
    }
}
