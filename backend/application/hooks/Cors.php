<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CORS
{
    public function handle()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin === 'http://localhost:3000') {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }

        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Content-Type: application/json");

        // Responder a OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}

