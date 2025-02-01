<?php
namespace App\Service;

use Firebase\JWT\JWT;

/**
 * Générateur de token pour Mercure
 */
class MercureTokenGenerator
{
    private $secretKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['MERCURE_JWT_SECRET'];
    }

    /**
     * Génère un token pour s'abonner à un topic Mercure
     * 
     * @param string $topic Le topic à écouter
     * 
     * @return string
     */
    public function generate(string $topic): string
    {
        $payload = [
            'mercure' => [
                'subscribe' => [$topic],
            ],
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
}
