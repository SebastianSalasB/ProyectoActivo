<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// ----------- CORS seguro para sesiones con frontend en http://localhost:3000 -----------
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:3000') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");
// Responder a preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
// -----------------------------------------------------------------------------------------
class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('IniciarSession');
        $this->load->library('session');
    }
    // ---------- Iniciar Session ----------
    public function IniciarSession() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->output
                ->set_status_header(405)
                ->set_output(json_encode(['status' => 'error', 'message' => 'Método no permitido.']));
        }

        $datos = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($datos['rut']) || !isset($datos['password'])) {
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 'error', 'message' => 'RUT y contraseña son obligatorios.']));
        }

        $rut = $datos['rut'];
        $password = $datos['password'];

        json_decode($rut);

        $usuarios = $this->IniciarSession->VeridicacionDatos($rut, $password); // <-- cambia a usar RUT

        if ($usuarios) {
            $usuarios = (array)$usuarios;
            unset($usuarios['usr_clave']); // Quitar la contraseña

            $this->session->set_userdata('user', $usuarios);

            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'user' => $usuarios
                ]));
        } else {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Credenciales inválidas.'
                ]));
        }
    }

    // ---------- Confirmacion Session ----------
    public function ConfirmacionSession() {
        $usuarios = $this->session->userdata('user');

        if ($usuarios) {
            return $this->output
                ->set_status_header(200)
                ->set_output(json_encode([
                    'status' => 'success',
                    'user' => $usuarios
                ]));
        } else {
            return $this->output
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'No hay sesión activa.'
                ]));
        }
    }

    // ---------- Cerrar Session ----------
    public function CerrarSession() {
        $this->session->unset_userdata('user');
        $this->session->sess_destroy();

        return $this->output
            ->set_status_header(200)
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.'
            ]));
    }
}
