<?php

require_once __DIR__ . '/../core/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // =========================
    // USER METHODS
    // =========================

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $name, string $email, string $password): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password)
             VALUES (:name, :email, :password)"
        );

        return $stmt->execute([
            'name'     => $name,
            'email'    => $email,
            'password' => $password
        ]);
    }

    // =========================
    // REFRESH TOKEN METHODS
    // =========================

    public function storeRefreshToken(int $userId, string $token, string $expiresAt): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO refresh_tokens (user_id, token, expires_at)
             VALUES (:user_id, :token, :expires_at)"
        );

        return $stmt->execute([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => $expiresAt
        ]);
    }

    public function findRefreshToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM refresh_tokens WHERE token = :token LIMIT 1"
        );

        $stmt->execute(['token' => $token]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function deleteRefreshToken(string $token): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM refresh_tokens WHERE token = :token"
        );

        return $stmt->execute(['token' => $token]);
    }

    public function deleteUserRefreshTokens(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM refresh_tokens WHERE user_id = :user_id"
        );

        return $stmt->execute(['user_id' => $userId]);
    }
}
