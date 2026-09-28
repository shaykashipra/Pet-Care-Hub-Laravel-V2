<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Doctor;
use App\Services\JwtService;
use App\Http\Resources\UserResource;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    // ─────────────────────────────────────────────
    //  POST /api/v1/register
    // ─────────────────────────────────────────────
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = new User();
        $user->fname    = $request->fname;
        $user->lname    = $request->lname;
        $user->email    = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone    = $request->phone;
        $user->image    = 'user.png';
        $user->save();

        $token = $this->jwt->generateToken($user->id, 'user');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/login
    // ─────────────────────────────────────────────
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $role  = $user->is_admin ? 'admin' : 'user';
        $token = $this->jwt->generateToken($user->id, $role);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/doctor/login
    // ─────────────────────────────────────────────
    public function doctorLogin(LoginRequest $request): JsonResponse
    {
        $doctor = Doctor::where('email', $request->email)->first();

        if (!$doctor || !Hash::check($request->password, $doctor->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $this->jwt->generateToken($doctor->id, 'doctor');

        return response()->json([
            'success' => true,
            'message' => 'Doctor login successful.',
            'token'   => $token,
            'doctor'  => [
                'id'             => $doctor->id,
                'doctor_name'    => $doctor->doctor_name,
                'email'          => $doctor->email,
                'specialization' => $doctor->specialization,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /api/v1/logout  (JWT is stateless)
    // ─────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        // JWT is stateless — client should discard the token.
        // For production, implement a token blacklist with Redis/cache.
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully. Please discard your token on the client side.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/v1/me
    // ─────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('jwt_user');
        $role = $request->attributes->get('jwt_role');

        if ($role === 'doctor') {
            return response()->json([
                'success' => true,
                'role'    => 'doctor',
                'doctor'  => [
                    'id'             => $user->id,
                    'doctor_name'    => $user->doctor_name,
                    'email'          => $user->email,
                    'specialization' => $user->specialization,
                    'gender'         => $user->gender,
                    'age'            => $user->age,
                    'experience'     => $user->experience,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'role'    => $role,
            'user'    => new UserResource($user),
        ]);
    }
}
