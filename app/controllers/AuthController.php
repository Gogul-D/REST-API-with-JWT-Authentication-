<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/Response.php';


class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * POST /api/register
     */
public function register()
{
    $data = $GLOBALS['request_body'] ?? [];

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


    /**
     * POST /api/login
     */
public function login()
{
    $data = $GLOBALS['request_body'] ?? [];

    if (empty($data['email']) || empty($data['password'])) {
        Response::error("Email and password required", 422);
    }

    $user = $this->user->findByEmail($data['email']);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        Response::error("Invalid credentials", 401);
    }

    $payload = [
        'user_id' => $user['id'],
        'email'   => $user['email'],
        'iat'     => time(),
        'exp'     => time() + $_ENV['JWT_EXPIRY']
    ];

    $token = JWT::encode($payload);

    Response::success("Login successful", [
        "token" => $token,
        "expires_in" => (int) $_ENV['JWT_EXPIRY']
    ]);
}



}
