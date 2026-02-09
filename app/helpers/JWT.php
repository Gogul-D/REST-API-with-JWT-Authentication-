<?php

class JWT
{
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generate JWT token
     */
    public static function encode(array $payload): string
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];

        $secret = $_ENV['JWT_SECRET'];
        $expiry = (int) $_ENV['JWT_EXPIRY'];

        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;

        $base64Header  = self::base64UrlEncode(json_encode($header));
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64Header . "." . $base64Payload,
            $secret,
            true
        );

        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Decode & validate JWT token
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        $secret = $_ENV['JWT_SECRET'];

        // Recreate signature
        $signatureCheck = self::base64UrlEncode(
            hash_hmac(
                'sha256',
                $base64Header . "." . $base64Payload,
                $secret,
                true
            )
        );

        if (!hash_equals($base64Signature, $signatureCheck)) {
            return null;
        }

        $payload = json_decode(
            self::base64UrlDecode($base64Payload),
            true
        );

        // Expiry check
        if ($payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}
