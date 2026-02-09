<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/JWT.php';


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

        if (
            empty($data['name']) ||
            empty($data['email']) ||
            empty($data['password'])
        ) {
            http_response_code(422);
            echo json_encode([
                "status" => false,
                "message" => "All fields are required"
            ]);
            return;
        }

        if ($this->user->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode([
                "status" => false,
                "message" => "Email already exists"
            ]);
            return;
        }

        $hashedPassword = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $this->user->create(
            $data['name'],
            $data['email'],
            $hashedPassword
        );

        echo json_encode([
            "status" => true,
            "message" => "User registered successfully"
        ]);
    }

    /**
     * POST /api/login
     */
public function login()
{
    $data = $GLOBALS['request_body'] ?? [];

    if (
        empty($data['email']) ||
        empty($data['password'])
    ) {
        http_response_code(422);
        echo json_encode([
            "status" => false,
            "message" => "Email and password required"
        ]);
        return;
    }

    $user = $this->user->findByEmail($data['email']);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode([
            "status" => false,
            "message" => "Invalid credentials"
        ]);
        return;
    }

    // 🔐 Generate JWT
    $token = JWT::encode([
        "user_id" => $user['id'],
        "email"   => $user['email']
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "token" => $token,
        "expires_in" => (int) $_ENV['JWT_EXPIRY']
    ]);
}

}
