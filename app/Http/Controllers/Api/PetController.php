<?php

namespace App\Http\Controllers\Api;

use App\Models\Pet;
use App\Http\Controllers\Controller;
use App\Http\Resources\PetResource;
use App\Http\Requests\Api\StorePetRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PetController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /api/v1/pets
    //  Public — with optional filters
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Pet::with(['users', 'characteristics']);

        // Optional filters
        if ($request->has('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->has('is_adopted')) {
            $query->where('is_adopted', (bool) $request->is_adopted);
        }
        if ($request->has('search')) {
            $query->where('pet_name', 'like', '%' . $request->search . '%');
        }

        $pets = $query->latest()->paginate(12);

        return response()->json([
            'success'    => true,
            'data'       => PetResource::collection($pets),
            'pagination' => [
                'total'        => $pets->total(),
                'per_page'     => $pets->perPage(),
                'current_page' => $pets->currentPage(),
                'last_page'    => $pets->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/pets/{id}
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $pet = Pet::with(['users', 'characteristics'])->find($id);

        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new PetResource($pet),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/pets  (auth required)
    // ─────────────────────────────────────────────
    public function store(StorePetRequest $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');

        $pet = new Pet();
        $pet->user_id     = $user->id;
        $pet->pet_name    = $request->pet_name;
        $pet->animal_type = $request->animal_type;
        $pet->age         = $request->age;
        $pet->gender      = $request->gender;
        $pet->color       = $request->color;
        $pet->coat_length = $request->coat_length;
        $pet->description = $request->description;
        $pet->is_adopted  = false;

        // Handle image upload
        if ($request->hasFile('pet_image')) {
            $image = $request->file('pet_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/pets'), $filename);
            $pet->pet_image = $filename;
        } else {
            $pet->pet_image = 'pet_default.png';
        }

        $pet->save();

        return response()->json([
            'success' => true,
            'message' => 'Pet added successfully.',
            'data'    => new PetResource($pet->load('users')),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PUT /api/v1/pets/{id}  (owner only)
    // ─────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $pet  = Pet::find($id);

        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }

        // Authorization: only the owner can update
        if ($pet->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this pet.',
            ], 403);
        }

        // Update only provided fields
        $pet->pet_name    = $request->pet_name    ?? $pet->pet_name;
        $pet->animal_type = $request->animal_type ?? $pet->animal_type;
        $pet->age         = $request->age         ?? $pet->age;
        $pet->gender      = $request->gender      ?? $pet->gender;
        $pet->color       = $request->color       ?? $pet->color;
        $pet->coat_length = $request->coat_length ?? $pet->coat_length;
        $pet->description = $request->description ?? $pet->description;
        $pet->is_adopted  = $request->has('is_adopted') ? (bool) $request->is_adopted : $pet->is_adopted;

        $pet->save();

        return response()->json([
            'success' => true,
            'message' => 'Pet updated successfully.',
            'data'    => new PetResource($pet->load('users')),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/v1/pets/{id}  (owner or admin)
    // ─────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');
        $pet  = Pet::find($id);

        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }

        // Only owner or admin can delete
        if ($pet->user_id !== $user->id && $role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this pet.',
            ], 403);
        }

        $pet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pet deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/pets/my  (auth — my pets)
    // ─────────────────────────────────────────────
    public function myPets(Request $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $pets = Pet::with(['characteristics'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => PetResource::collection($pets),
        ]);
    }
}
