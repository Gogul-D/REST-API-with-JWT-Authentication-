<?php

require_once __DIR__ . '/../models/Patient.php';

class PatientController
{
    private Patient $patient;

    public function __construct()
    {
        $this->patient = new Patient();
    }

    public function index()
    {
        echo json_encode([
            "status" => true,
            "data" => $this->patient->getAll()
        ]);
    }

    public function store()
    {
        $data = $GLOBALS['request_body'] ?? [];

        if (
            empty($data['name']) ||
            empty($data['age']) ||
            empty($data['gender'])
        ) {
            http_response_code(422);
            echo json_encode([
                "status" => false,
                "message" => "Required fields missing"
            ]);
            return;
        }

        $this->patient->create($data);

        echo json_encode([
            "status" => true,
            "message" => "Patient created successfully"
        ]);
    }

    public function update($id)
    {
        $data = $GLOBALS['request_body'] ?? [];

        $this->patient->update((int)$id, $data);

        echo json_encode([
            "status" => true,
            "message" => "Patient updated successfully"
        ]);
    }

    public function destroy($id)
    {
        $this->patient->delete((int)$id);

        echo json_encode([
            "status" => true,
            "message" => "Patient deleted successfully"
        ]);
    }
}
