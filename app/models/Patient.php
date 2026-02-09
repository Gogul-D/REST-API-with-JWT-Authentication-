<?php

require_once __DIR__ . '/../core/Database.php';

class Patient
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM patients");
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO patients (name, age, gender, phone, address)
             VALUES (:name, :age, :gender, :phone, :address)"
        );

        return $stmt->execute([
            'name'    => $data['name'],
            'age'     => $data['age'],
            'gender'  => $data['gender'],
            'phone'   => $data['phone'],
            'address' => $data['address']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE patients SET
                name = :name,
                age = :age,
                gender = :gender,
                phone = :phone,
                address = :address
             WHERE id = :id"
        );

        return $stmt->execute([
            'id'      => $id,
            'name'    => $data['name'],
            'age'     => $data['age'],
            'gender'  => $data['gender'],
            'phone'   => $data['phone'],
            'address' => $data['address']
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM patients WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }
}
