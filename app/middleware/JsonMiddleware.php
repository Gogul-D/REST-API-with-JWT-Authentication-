<?php

class JsonMiddleware
{
    public static function handle()
    {
        header("Content-Type: application/json");

        $method = $_SERVER['REQUEST_METHOD'];

        // Only validate JSON for write operations
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {

            if (!isset($_SERVER['CONTENT_TYPE']) ||
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {

                http_response_code(415);
                echo json_encode([
                    "status" => false,
                    "message" => "Content-Type must be application/json"
                ]);
                exit;
            }

            $rawInput = file_get_contents("php://input");
            $data = json_decode($rawInput, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    "status" => false,
                    "message" => "Invalid JSON payload"
                ]);
                exit;
            }

            // Attach decoded body globally
            $GLOBALS['request_body'] = $data;
        }
    }
}
