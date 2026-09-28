<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Requests\Api\StoreAppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /api/v1/appointments  (auth)
    //  Users: own appointments | Doctors: their appointments | Admin: all
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        $query = Appointment::with(['patient', 'doctor']);

        if ($role === 'doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($role === 'admin') {
            // admin sees all
        } else {
            $query->where('user_id', $user->id);
        }

        // Optional status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->latest()->paginate(10);

        return response()->json([
            'success'    => true,
            'data'       => AppointmentResource::collection($appointments),
            'pagination' => [
                'total'        => $appointments->total(),
                'per_page'     => $appointments->perPage(),
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/appointments/{id}
    // ─────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        $appointment = Appointment::with(['patient', 'doctor'])->find($id);

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        // Authorization check
        $canView = $role === 'admin'
            || ($role === 'doctor' && $appointment->doctor_id === $user->id)
            || ($role === 'user'   && $appointment->user_id   === $user->id);

        if (!$canView) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => new AppointmentResource($appointment),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/appointments  (user only)
    // ─────────────────────────────────────────────
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        if ($role !== 'user') {
            return response()->json(['success' => false, 'message' => 'Only users can book appointments.'], 403);
        }

        $appointment = new Appointment();
        $appointment->user_id   = $user->id;
        $appointment->doctor_id = $request->doctor_id;
        $appointment->pet_name  = $request->pet_name;
        $appointment->contact   = $request->contact;
        $appointment->date      = $request->date;
        $appointment->time      = $request->time;
        $appointment->symptoms  = $request->symptoms;
        $appointment->category  = $request->category;
        $appointment->status    = 'pending';
        $appointment->save();

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'data'    => new AppointmentResource($appointment->load(['patient', 'doctor'])),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PUT /api/v1/appointments/{id}
    //  Doctors: update status/prescription | Users: can only cancel pending
    // ─────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        $appointment = Appointment::with(['patient', 'doctor'])->find($id);

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        if ($role === 'doctor') {
            if ($appointment->doctor_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            // Doctors can update status and prescription
            if ($request->has('status')) {
                $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
                if (!in_array($request->status, $validStatuses)) {
                    return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
                }
                $appointment->status = $request->status;
            }
            if ($request->has('prescription')) {
                $appointment->prescription = $request->prescription;
            }

        } elseif ($role === 'admin') {
            // Admin can update anything
            if ($request->has('status'))       $appointment->status       = $request->status;
            if ($request->has('prescription')) $appointment->prescription = $request->prescription;

        } elseif ($role === 'user') {
            if ($appointment->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            // Users can only cancel their own pending appointment
            if ($appointment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only modify pending appointments.',
                ], 422);
            }
            $appointment->status = 'cancelled';
        }

        $appointment->save();

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully.',
            'data'    => new AppointmentResource($appointment),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/v1/appointments/{id}  (admin or user cancelling own)
    // ─────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        $canDelete = $role === 'admin'
            || ($role === 'user' && $appointment->user_id === $user->id && $appointment->status === 'pending');

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete this appointment.',
            ], 403);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully.',
        ]);
    }
}
