<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        $this->load->model('RespoModel');
        header('Access-Control-Allow-Origin: *'); // Permite CORS para probar frontend
        header('Content-Type: application/json');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    public function listarResponsable() {
        // Obtiene los registros de responsables con paginación
        $responsables = $this->RespoModel->responsablesListado();

        // Obtiene el total de registros (para calcular páginas en el frontend)
        $total = $this->RespoModel->contarResponsables();

        // Retorna los datos en formato JSON
        echo json_encode([
            'Responsable' => $responsables,
            'total' => $total
        ]);
    }
    public function ActualizarUsuario($id) {
        $data = json_decode(file_get_contents('php://input'), true); // <--- importante

        if (!$data) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }

        $userData = [
            'usr_nombre' => $data['usr_nombre'] ?? null,
            'usr_apellido'  => $data['usr_apellido'] ?? null,
            'usr_rut'   => $data['usr_rut'] ?? null,
            'usr_correo'    => $data['usr_correo'] ?? null,
            'usr_telefono'  => $data['usr_telefono'] ?? null,
        ];

        if ($this->RespoModel->actualizaUsuario($id, $userData)) {
            echo json_encode(['status' => 'updated']);
            } else {
                echo json_encode(['status' => 'error']);
            }
    }
    public function CrearUsuario(){

        // Permitir CORS (solo si estás usando frontend separado)
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        // Leer los datos JSON del frontend
        $datos = json_decode(file_get_contents("php://input"), true);

        // Validación básica de estructura
        if (!isset($datos['activos']) || !is_array($datos['activos'])) {
            echo json_encode(['error' => 'Datos inválidos.']);
            return;
        }

        // Validar que los campos requeridos no estén vacíos
        foreach ($datos['activos'] as $index => $usuario) {
            if (
                empty($usuario['user_nombre']) ||
                empty($usuario['user_apellido']) ||
                empty($usuario['user_rut']) ||
                empty($usuario['user_correo'])
            ) {
                echo json_encode(['error' => "Faltan campos requeridos en el registro #$index"]);
                return;
            }
        }

        // Llama al modelo para insertar
        $this->load->model('RespoModel');
        $resultado = $this->RespoModel->insertarResponsables($datos['activos']);

        echo json_encode(['success' => $resultado]);
    }
    public function EliminarUsuario($id) {
        $result = $this->RespoModel->eliminarUsuario($id);

        if ($result) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    public function listaE() {
        $this->load->model('RespoModel');
        $tipos = $this->RespoModel->listaE();

        echo json_encode($tipos);
    }
    public function listaS() {
       $this->load->model('RespoModel');
        $tipos = $this->RespoModel->listaS();

        echo json_encode($tipos);
    }
    public function listaT() {
        $this->load->model('RespoModel');
        $tipos = $this->RespoModel->listaT();

        echo json_encode($tipos);
    }
        
}
