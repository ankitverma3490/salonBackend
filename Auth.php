<?php
require_once __DIR__ . '/config.php';

class Auth
{
    public static function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }


    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function generateToken($userId, $email, $role = 'customer')
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function verifyToken($token)
    {
        if (!$token) {
            return false;
        }

        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $tokenParts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true);
        $base64UrlSignature = self::base64UrlEncode($validSignature);

        if ($base64UrlSignature !== $signature) {
            return false;
        }

        $payloadData = json_decode(self::base64UrlDecode($payload), true);

        if ($payloadData['exp'] < time()) {
            return false;
        }

        return $payloadData;
    }

    public static function getUserFromToken()
    {
        $token = null;
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        }

        // Search for Authorization header (case-insensitive)
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $token = str_replace(['Bearer ', 'bearer '], '', $value);
                break;
            }
        }

        // Fallback to server variables
        if (!$token) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $token = str_replace(['Bearer ', 'bearer '], '', $_SERVER['HTTP_AUTHORIZATION']);
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $token = str_replace(['Bearer ', 'bearer '], '', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            }
        }

        return self::verifyToken($token);
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
