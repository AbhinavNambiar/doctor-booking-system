<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can create an appointment.
     */
    public function create(User $user)
    {
        // Only patients can create appointments
        return $user->role === 'patient';
    }

    /**
     * Determine if the user can view the appointment.
     */
    public function view(User $user, Appointment $appointment)
    {
        // Patients can view their own appointments
        if ($user->role === 'patient' && $appointment->patient_id === $user->id) {
            return true;
        }

        // Doctors can view their own appointments
        if ($user->role === 'doctor' && $appointment->doctor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update the status of the appointment.
     */
    public function updateStatus(User $user, Appointment $appointment)
    {
        // Patients can update their own appointments
        if ($user->role === 'patient' && $appointment->patient_id === $user->id) {
            return true;
        }

        // Doctors can update their own appointments
        if ($user->role === 'doctor' && $appointment->doctor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view appointments for doctors.
     */
    public function viewForDoctor(User $user)
    {
        // Only doctors can view their appointments
        return $user->role === 'doctor';
    }

    /**
     * Determine if the user can update the status of the appointment for doctors.
     */
    public function updateStatusForDoctor(User $user, Appointment $appointment)
    {
        // Only the doctor for the appointment can update the status
        return $user->role === 'doctor' && $appointment->doctor_id === $user->id;
    }
}
