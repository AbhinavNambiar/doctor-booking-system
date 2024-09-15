<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Create a new appointment.
     * Only patients can create an appointment.
     */
    public function create(Request $request)
    {
        // Validate request
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
        ]);

        // Check if the user is a patient
        if (Auth::user()->role !== 'patient') {
            return response()->json(['error' => 'Only patients can create appointments'], 403);
        }

        // Check if the appointment slot is already taken
        $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->first();

        if ($existingAppointment) {
            return response()->json(['error' => 'This appointment slot is already booked'], 409);
        }

        // Create the appointment
        $appointment = new Appointment();
        $appointment->patient_id = Auth::id();
        $appointment->doctor_id = $request->doctor_id;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->status = 'RSVP';
        $appointment->save();

        return response()->json(['message' => 'Appointment created successfully', 'appointment' => $appointment], 201);
    }

    /**
     * Update the status of an appointment.
     * Patients can cancel/reject/postpone their own appointments.
     */
    public function updateStatus(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'status' => 'required|in:cancelled,rejected,postponed',
        ]);

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Check if the user is the patient who created the appointment
        if (Auth::user()->id !== $appointment->patient_id && Auth::user()->role !== 'doctor') {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        // Update the status
        $appointment->status = $request->status;
        $appointment->save();

        return response()->json(['message' => 'Appointment status updated', 'appointment' => $appointment], 200);
    }

    /**
     * View list of appointments for the authenticated user.
     * Patients see their own appointments. Doctors see their scheduled appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::query();

        // Filter by user role
        if (Auth::user()->role === 'patient') {
            $query->where('patient_id', Auth::id());
        } elseif (Auth::user()->role === 'doctor') {
            $query->where('doctor_id', Auth::id());
        } else {
            return response()->json(['error' => 'Invalid user role'], 403);
        }

        // Apply date filter if provided
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->get();

        return response()->json(['appointments' => $appointments], 200);
    }

    /**
     * View appointments for the doctor.
     */
    public function viewForDoctor(Request $request)
    {
        // Only doctors can view their appointments
        if (Auth::user()->role !== 'doctor') {
            return response()->json(['error' => 'Only doctors can view this resource'], 403);
        }

        $query = Appointment::where('doctor_id', Auth::id());

        // Apply date filter if provided
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->get();

        return response()->json(['appointments' => $appointments], 200);
    }

    /**
     * Doctors can update the status of an appointment.
     */
    public function updateStatusForDoctor(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'status' => 'required|in:approved,cancelled,rejected',
        ]);

        // Find the appointment
        $appointment = Appointment::findOrFail($id);

        // Check if the user is the doctor for the appointment
        if (Auth::user()->id !== $appointment->doctor_id) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        // Update the status
        $appointment->status = $request->status;
        $appointment->save();

        return response()->json(['message' => 'Appointment status updated by doctor', 'appointment' => $appointment], 200);
    }
}
