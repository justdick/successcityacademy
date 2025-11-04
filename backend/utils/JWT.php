<?php

require_once __DIR__ . '/../config/config.php';

class JWT {
    /**
     * Encode a payload into a JWT token
     * 
     * @param array $payload The data to encode
     * @param string $secret The secret key for signing
     * @return string The JWT token
     */
    public static function encode($payload, $secret = JWT_SECRET) {
        // Add issued at and expiration time
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRATION;

        // Create header
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        // Encode header and payload
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        // Create signature
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        // Return complete token
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Decode and verify a JWT token
     * 
     * @param string $token The JWT token to decode
     * @param string $secret The secret key for verification
     * @return object|null The decoded payload or null on failure
     */
    public static function decode($token, $secret = JWT_SECRET) {
        try {
            // Split token into parts
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                error_log("JWT Error: Invalid token format");
                return null;
            }

            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

            // Verify signature
            $signature = self::base64UrlDecode($signatureEncoded);
            $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);

            if (!hash_equals($expectedSignature, $signature)) {
                error_log("JWT Error: Invalid signature");
                return null;
            }

            // Decode payload
            $payload = json_decode(self::base64UrlDecode($payloadEncoded));

            if (!$payload) {
                error_log("JWT Error: Invalid payload");
                return null;
            }

            // Check expiration
            if (isset($payload->exp) && $payload->exp < time()) {
                error_log("JWT Error: Token expired");
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            error_log("JWT Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Base64 URL encode
     * 
     * @param string $data The data to encode
     * @return string The encoded string
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * 
     * @param string $data The data to decode
     * @return string The decoded string
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

