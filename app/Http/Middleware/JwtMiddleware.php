<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Doctor;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = JwtService::extractBearerToken($request->header('Authorization'));

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not provided. Please include a Bearer token in the Authorization header.',
            ], 401);
        }

        $payload = $this->jwt->validateToken($token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please login again.',
            ], 401);
        }

        // Load the authenticated entity (user or doctor) and bind it to the request
        $role = $payload->role ?? 'user';

        if ($role === 'doctor') {
            $authEntity = Doctor::find($payload->sub);
        } else {
            $authEntity = User::find($payload->sub);
        }

        if (!$authEntity) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated entity not found.',
            ], 401);
        }

        // Store on request for use in controllers
        $request->merge(['_jwt_user' => $authEntity, '_jwt_role' => $role, '_jwt_payload' => $payload]);
        $request->attributes->set('jwt_user', $authEntity);
        $request->attributes->set('jwt_role', $role);

        return $next($request);
    }
}
