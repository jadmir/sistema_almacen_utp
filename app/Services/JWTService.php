<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTService
{
    private string $secretKey;
    private string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secretKey = env('JWT_SECRET', 'your-secret-key');
    }

    /**
     * Genera un token JWT
     */
    public function generateToken(array $payload, int $expirationMinutes = 60): string
    {
        $issuedAt = time();
        $expire = $issuedAt + ($expirationMinutes * 60);

        $tokenPayload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expire,
        ]);

        return JWT::encode($tokenPayload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verifica y decodifica un token JWT
     */
    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, $this->algorithm));
        } catch (Exception $e) {
            throw new Exception('Token inválido: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el token del header Authorization
     */
    public function getTokenFromRequest(): ?string
    {
        $authHeader = request()->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }
}
