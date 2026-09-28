<?php

namespace App\Http\Controllers\Api;

use App\Models\Pet;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\PetResource;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Verify the requesting JWT user has admin role.
     * Returns a 403 JSON response if not, or null if authorized.
     */
    private function requireAdmin(Request $request): ?JsonResponse
    {
        $role = $request->attributes->get('jwt_role');
        if ($role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required.',
            ], 403);
        }
        return null;
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/admin/stats
    // ─────────────────────────────────────────────
    public function stats(Request $request): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        return response()->json([
            'success' => true,
            'data'    => [
                'total_users'        => User::count(),
                'total_pets'         => Pet::count(),
                'total_doctors'      => Doctor::count(),
                'total_appointments' => Appointment::count(),
                'pending_appointments'   => Appointment::where('status', 'pending')->count(),
                'completed_appointments' => Appointment::where('status', 'completed')->count(),
                'adopted_pets'       => Pet::where('is_adopted', true)->count(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/admin/users
    // ─────────────────────────────────────────────
    public function users(Request $request): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $query = User::query();
        if ($request->has('search')) {
            $query->where('fname', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }
        $users = $query->latest()->paginate(15);

        return response()->json([
            'success'    => true,
            'data'       => UserResource::collection($users),
            'pagination' => [
                'total'        => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/v1/admin/users/{id}
    // ─────────────────────────────────────────────
    public function destroyUser(Request $request, int $id): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/admin/pets
    // ─────────────────────────────────────────────
    public function pets(Request $request): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $pets = Pet::with(['users'])->latest()->paginate(15);

        return response()->json([
            'success'    => true,
            'data'       => PetResource::collection($pets),
            'pagination' => [
                'total'        => $pets->total(),
                'current_page' => $pets->currentPage(),
                'last_page'    => $pets->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  PATCH /api/v1/admin/pets/{id}  (mark adopted etc.)
    // ─────────────────────────────────────────────
    public function updatePet(Request $request, int $id): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $pet = Pet::find($id);
        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }
        if ($request->has('is_adopted')) $pet->is_adopted = (bool) $request->is_adopted;
        if ($request->has('pet_name'))   $pet->pet_name   = $request->pet_name;
        $pet->save();

        return response()->json([
            'success' => true,
            'message' => 'Pet updated.',
            'data'    => new PetResource($pet),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/v1/admin/pets/{id}
    // ─────────────────────────────────────────────
    public function destroyPet(Request $request, int $id): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $pet = Pet::find($id);
        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }
        $pet->delete();
        return response()->json(['success' => true, 'message' => 'Pet deleted.']);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/admin/appointments
    // ─────────────────────────────────────────────
    public function appointments(Request $request): JsonResponse
    {
        if ($guard = $this->requireAdmin($request)) return $guard;

        $query = Appointment::with(['patient', 'doctor']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        $appointments = $query->latest()->paginate(15);

        return response()->json([
            'success'    => true,
            'data'       => AppointmentResource::collection($appointments),
            'pagination' => [
                'total'        => $appointments->total(),
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
            ],
        ]);
    }
}
