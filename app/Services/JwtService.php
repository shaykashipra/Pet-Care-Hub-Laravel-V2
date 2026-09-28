<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $ttl; // in seconds

    public function __construct()
    {
        $this->secret = config('app.key');
        $this->ttl    = (int) config('jwt.ttl', 3600); // default 1 hour
    }

    /**
     * Generate a JWT token for a given subject (user/doctor id) and role.
     */
    public function generateToken(int $id, string $role = 'user'): string
    {
        $now = time();

        $payload = [
            'iss' => config('app.url'),  // issuer
            'iat' => $now,               // issued at
            'exp' => $now + $this->ttl,  // expiration
            'sub' => $id,                // subject (user/doctor id)
            'role' => $role,             // 'user' | 'doctor' | 'admin'
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Decode and validate a JWT token.
     * Returns the decoded payload as a stdClass object, or null on failure.
     */
    public function validateToken(string $token): ?\stdClass
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $decoded;
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract the Bearer token from an Authorization header value.
     * Returns null if the header is missing or malformed.
     */
    public static function extractBearerToken(?string $header): ?string
    {
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }
        return substr($header, 7);
    }
}
