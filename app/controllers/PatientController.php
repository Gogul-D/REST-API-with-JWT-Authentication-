<?php

require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Logger.php';

class PatientController
{
    private Patient $patient;

    public function __construct()
    {
        $this->patient = new Patient();
    }

    public function index()
    {
        $patients = $this->patient->getAll();
        Response::success("Patients fetched successfully", $patients);
    }

public function store()
{
    $data = $GLOBALS['request_body'] ?? [];

    Validator::validatePatient($data);

    $this->patient->create($data);

    $user = $GLOBALS['auth_user']['user_id'] ?? 'unknown';

    Logger::audit("User {$user} created patient: {$data['name']}");

    Response::success("Patient created successfully", null, 201);
}




public function update($id)
{
    if (!is_numeric($id)) {
        Response::error("Invalid patient ID", 400);
    }

    $data = $GLOBALS['request_body'] ?? [];

    Validator::validatePatient($data, true);

    $this->patient->update((int)$id, $data);

    $user = $GLOBALS['auth_user']['user_id'] ?? 'unknown';

    Logger::audit("User {$user} updated patient ID: {$id}");

    Response::success("Patient updated successfully");
}


public function destroy($id)
{
    if (!is_numeric($id)) {
        Response::error("Invalid patient ID", 400);
    }

    $this->patient->delete((int)$id);

    $user = $GLOBALS['auth_user']['user_id'] ?? 'unknown';

    Logger::audit("User {$user} deleted patient ID: {$id}");

    Response::success("Patient deleted successfully");
}


}
