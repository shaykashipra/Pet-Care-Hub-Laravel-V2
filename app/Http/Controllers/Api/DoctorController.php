<?php

namespace App\Http\Controllers\Api;

use App\Models\Doctor;
use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /api/v1/doctors  (public)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query();

        if ($request->has('specialization')) {
            $query->where('specialization', 'like', '%' . $request->specialization . '%');
        }
        if ($request->has('search')) {
            $query->where('doctor_name', 'like', '%' . $request->search . '%');
        }

        $doctors = $query->paginate(10);

        return response()->json([
            'success'    => true,
            'data'       => DoctorResource::collection($doctors),
            'pagination' => [
                'total'        => $doctors->total(),
                'per_page'     => $doctors->perPage(),
                'current_page' => $doctors->currentPage(),
                'last_page'    => $doctors->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/doctors/{id}  (public)
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new DoctorResource($doctor),
        ]);
    }
}
