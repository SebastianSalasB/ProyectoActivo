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
    public function listarResponsable($pagina = 1) {
        $limit = 8;
        $offset = ($pagina - 1) * $limit;

        $this->load->model('RespoModel');
        $responsables = $this->RespoModel->getResponsablesPaginados($limit, $offset);
        $total = $this->RespoModel->contarResponsables(); // Cuenta total de registros

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
        // CORS para desarrollo (ajustar en producción)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            exit(0);
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        // Decodificar JSON
        $input = json_decode(file_get_contents('php://input'), true);

        // Log de depuración
        log_message('error', 'JSON recibido: ' . print_r($input, true));

        // Validación de estructura
        if (!isset($input['activos']) || !is_array($input['activos'])) {
            show_error('Datos inválidos. Se esperaba un arreglo de activos.', 400);
            return;
        }

        foreach ($input['activos'] as $user) {
            // Validación de campos mínimos obligatorios
            if (
                empty($user['user_nombre']) ||
                empty($user['user_apellido']) ||
                empty($user['user_email']) ||
                empty($user['user_telefono']) ||
                empty($user['user_rut']) ||
                empty($user['user_id_sucursal'])
            ) {
                log_message('error', 'Usuario incompleto: ' . print_r($user, true));
                continue;
            }

            // Si el usuario es tipo admin (user_id_tipos == 1) y no viene clave, ignorar
            if (
                isset($user['user_id_tipos']) &&
                $user['user_id_tipos'] == 1 &&
                empty($user['user_clave'])
            ) {
                log_message('error', 'Administrador sin clave: ' . print_r($user, true));
                continue;
            }

            // Preparar datos para insertar
            $data = [
                'usr_nombre'        => $user['user_nombre'],
                'usr_apellido'      => $user['user_apellido'],
                'usr_correo'        => $user['user_email'],
                'usr_telefono'      => $user['user_telefono'],
                'usr_rut'           => $user['user_rut'],
                'usr_id_sucursal'   => $user['user_id_sucursal'],
                'usr_estado'        => 'activo',
            ];

            // Asignar tipo si viene
            if (!empty($user['user_id_tipos'])) {
                $data['usr_id_tipos'] = $user['user_id_tipos'];
            }

            // Hashear la clave si viene
            if (!empty($user['user_clave'])) {
                $data['usr_clave'] = password_hash($user['user_clave'], PASSWORD_BCRYPT);
            }

            // Guardar en BD
            $this->RespoModel->insertarUsuario($data);
        }

        echo json_encode(['status' => 'success']);
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
