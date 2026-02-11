<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/Response.php';

class AuthService
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function register(array $data)
    {
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            Response::error("All fields are required", 422);
        }

        if ($this->user->findByEmail($data['email'])) {
            Response::error("Email already exists", 409);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $this->user->create(
            $data['name'],
            $data['email'],
            $hashedPassword
        );

        Response::success("User registered successfully", null, 201);
    }

    public function login(array $data)
    {
        if (empty($data['email']) || empty($data['password'])) {
            Response::error("Email and password required", 422);
        }

        $user = $this->user->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error("Invalid credentials", 401);
        }

        $payload = [
            'user_id' => $user['id'],
            'email'   => $user['email']
        ];

        $accessToken = JWT::encode($payload);

        $refreshToken = bin2hex(random_bytes(64));
        $refreshExpiry = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->user->storeRefreshToken($user['id'], $refreshToken, $refreshExpiry);

        setcookie("refresh_token", $refreshToken, [
            'expires'  => time() + (7 * 24 * 60 * 60),
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        Response::success("Login successful", [
            "access_token" => $accessToken,
            "expires_in"   => (int) $_ENV['JWT_EXPIRY']
        ]);
    }

    public function refresh()
    {
        if (!isset($_COOKIE['refresh_token'])) {
            Response::error("Refresh token missing", 401);
        }

        $refreshToken = $_COOKIE['refresh_token'];

        $tokenData = $this->user->findRefreshToken($refreshToken);

        if (!$tokenData) {
            Response::error("Invalid refresh token", 401);
        }

        if (strtotime($tokenData['expires_at']) < time()) {
            $this->user->deleteRefreshToken($refreshToken);
            Response::error("Refresh token expired", 401);
        }

        // rotate
        $this->user->deleteRefreshToken($refreshToken);

        $newToken = bin2hex(random_bytes(64));
        $newExpiry = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->user->storeRefreshToken($tokenData['user_id'], $newToken, $newExpiry);

        setcookie("refresh_token", $newToken, [
            'expires'  => time() + (7 * 24 * 60 * 60),
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        $accessToken = JWT::encode([
            'user_id' => $tokenData['user_id']
        ]);

        Response::success("Token refreshed successfully", [
            "access_token" => $accessToken,
            "expires_in"   => (int) $_ENV['JWT_EXPIRY']
        ]);
    }

    public function logout()
    {
        if (!isset($_COOKIE['refresh_token'])) {
            Response::error("Refresh token missing", 401);
        }

        $refreshToken = $_COOKIE['refresh_token'];

        $this->user->deleteRefreshToken($refreshToken);

        setcookie("refresh_token", "", [
            'expires' => time() - 3600,
            'path'    => '/'
        ]);

        Response::success("Logged out successfully");
    }
}
