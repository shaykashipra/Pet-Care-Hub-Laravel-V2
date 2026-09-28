<?php

namespace App\Http\Controllers\Api;

use App\Models\Pet;
use App\Models\Favourite;
use App\Http\Controllers\Controller;
use App\Http\Resources\PetResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavouriteController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /api/v1/favourites  (auth user)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $pets = $user->favourites()->with(['characteristics'])->get();

        return response()->json([
            'success' => true,
            'count'   => $pets->count(),
            'data'    => PetResource::collection($pets),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/favourites/{petId}
    // ─────────────────────────────────────────────
    public function store(Request $request, int $petId): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');

        $pet = Pet::find($petId);
        if (!$pet) {
            return response()->json(['success' => false, 'message' => 'Pet not found.'], 404);
        }

        // Avoid duplicates
        if ($user->favourites()->where('pet_id', $petId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pet is already in your favourites.',
            ], 409);
        }

        $user->favourites()->attach($petId);

        return response()->json([
            'success' => true,
            'message' => 'Pet added to favourites.',
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/v1/favourites/{petId}
    // ─────────────────────────────────────────────
    public function destroy(Request $request, int $petId): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');

        if (!$user->favourites()->where('pet_id', $petId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Pet is not in your favourites.',
            ], 404);
        }

        $user->favourites()->detach($petId);

        return response()->json([
            'success' => true,
            'message' => 'Pet removed from favourites.',
        ]);
    }
}
