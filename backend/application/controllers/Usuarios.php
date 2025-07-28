<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller
{
    public function __construct(){   
       
        parent::__construct();
        $this->load->model('RespoModel');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
        // Permitir CORS si es necesario
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['activos']) || !is_array($input['activos'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos. Se esperaba un arreglo de activos.'
            ]);
            return;
        }

        $usuariosInsertados = 0;

        foreach ($input['activos'] as $user) {
            // Validar campos obligatorios
            if (
                empty($user['user_nombre']) ||
                empty($user['user_apellido']) ||
                empty($user['user_rut']) ||
                empty($user['user_correo']) ||
                empty($user['user_telefono']) ||
                empty($user['user_id_sucursal'])
            ) {
                log_message('error', 'Datos incompletos en usuario: ' . print_r($user, true));
                continue;
            }

            // Preparar datos
            $data = [
                'usr_nombre'        => $user['user_nombre'],
                'usr_apellido'      => $user['user_apellido'],
                'usr_correo'        => $user['user_correo'],
                'usr_rut'           => $user['user_rut'],
                'usr_telefono'      => $user['user_telefono'],
                'usr_id_sucursal'   => $user['user_id_sucursal'],
                'usr_estado'        => 'activo',
                'usr_id_tipos'      => isset($user['user_id_tipos']) ? $user['user_id_tipos'] : 2,
            ];

            // Incluir clave si es responsable
            if (!empty($user['user_clave'])) {
                $data['usr_clave'] = password_hash($user['user_clave'], PASSWORD_BCRYPT);
            }

            // Insertar en la base de datos
            if ($this->RespoModel->insertarUsuario($data)) {
                $usuariosInsertados++;
            }
        }

        if ($usuariosInsertados > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => "Se insertaron $usuariosInsertados usuario(s)"
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se insertó ningún usuario válido'
            ]);
        }
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
