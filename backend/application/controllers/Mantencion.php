<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mantencion extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('MantencionModel');

         // Librerías necesarias
        $this->load->library('session');

        // Detectar origen automáticamente
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed_origins = ['http://localhost:3000']; 
        if (!empty($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }

        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Content-Type: application/json; charset=UTF-8");

        // Manejo de preflight (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit; 
        }

        // Validar sesión
        if (!$this->session->userdata('user')) {
            http_response_code(401); // No autorizado
            echo json_encode(['error' => 'Sesión expirada o no iniciada']);
            exit;
        }

    }

    public function TABLA($id) { 
        $datos = $this->MantencionModel->obtenerMantencion($id);
        log_message('error', 'Datos recibidos: ' . print_r($datos, true));
        echo json_encode($datos);
    }
    
    public function Mantencion() {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!$datos) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            return;
        }

        if (empty($datos['man_id_activo']) || empty($datos['man_id_usuario']) || empty($datos['man_fecha']) || empty($datos['man_descripcion'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios']);
            return;
        }

        try {
            $insertar = $this->MantencionModel->Mantencion($datos);

            if ($insertar) {
                echo json_encode(['status' => 'success', 'message' => 'Mantención registrada']);
            } else {
                $dbError = $this->db->error();
                log_message('error', 'Error DB mantencion: '.print_r($dbError, true));
                echo json_encode(['status' => 'error', 'message' => 'Error al insertar en la base de datos', 'db_error' => $dbError]);
            }
        } catch (Exception $e) {
            log_message('error', 'Excepción mantencion: '.$e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error en el servidor', 'exception' => $e->getMessage()]);
        }
    }



}
