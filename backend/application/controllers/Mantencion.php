<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mantencion extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('MantencionModel');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    }

    public function Mantencion() {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!$datos) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
            return;
        }

        // Validaciones
        if (empty($datos['man_id_activo']) || empty($datos['man_id_usuario']) || empty($datos['man_fecha'])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios']);
            return;
        }

        $insertar = $this->MantencionModel->Mantencion($datos);

        if ($insertar) {
            echo json_encode(['status' => 'success', 'message' => 'Mantención registrada']);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al insertar en la base de datos']);
        }
    }
}
