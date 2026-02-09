<?php

require_once __DIR__ . '/../core/Database.php';

class Patient
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ============================
       GET ALL PATIENTS
    ============================ */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM patients ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============================
       CREATE PATIENT
    ============================ */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO patients (name, age, gender, phone, address)
                VALUES (:name, :age, :gender, :phone, :address)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':age', (int)$data['age'], PDO::PARAM_INT);
        $stmt->bindValue(':gender', $data['gender']);
        $stmt->bindValue(':phone', $data['phone'] ?? null);
        $stmt->bindValue(':address', $data['address'] ?? null);

        return $stmt->execute();
    }

    /* ============================
       UPDATE PATIENT (PARTIAL)
    ============================ */
    public function update(int $id, array $data): bool
    {
        if (!$this->exists($id)) {
            throw new Exception("Patient not found");
        }

        $fields = [];
        $params = [':id' => $id];

        foreach (['name','age','gender','phone','address'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            throw new Exception("No fields provided for update");
        }

        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        return $stmt->execute();
    }

    /* ============================
       DELETE PATIENT
    ============================ */
    public function delete(int $id): bool
    {
        if (!$this->exists($id)) {
            throw new Exception("Patient not found");
        }

        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /* ============================
       CHECK IF PATIENT EXISTS
    ============================ */
    private function exists(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM patients WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}
