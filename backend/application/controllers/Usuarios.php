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
        header("Access-Control-Allow-Credentials: true");
    }
    private function setCorsHeaders() {
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
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
    public function DatosUsuario($id = null) {
        $this->setCorsHeaders(); // 👈 agrega esto primero

        if ($id === null || !is_numeric($id)) {
            show_error('ID inválido', 400);
            return;
        }

        $datos = $this->RespoModel->DatosUsuario($id);

        if (!$datos) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Usuario no encontrado']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($datos));
    }
    public function ActualizarPerfil($id) {
        $raw = file_get_contents('php://input');
        error_log("RAW INPUT: " . $raw);
        $data = json_decode($raw, true);
        error_log("Datos recibidos: " . print_r($data, true));

        if (!$data) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }
        if (!is_numeric($id)) {
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $userData = [
            'usr_nombre'      => $data['usr_nombre']     ?? null,
            'usr_apellido'    => $data['usr_apellido']   ?? null,
            'usr_rut'         => $data['usr_rut']        ?? null,
            'usr_correo'      => $data['usr_correo']     ?? null,
            'usr_telefono'    => $data['usr_telefono']   ?? null,
            'usr_id_tipos'    => $data['usr_id_tipos']   ?? 2,
            'usr_id_sucursal' => $data['usr_id_sucursal']?? null,
            'usr_estado'      => $data['usr_estado']     ?? 'activo',
        ];

        if (empty($userData['usr_nombre']) || empty($userData['usr_apellido']) || empty($userData['usr_rut'])) {
            echo json_encode(['error' => 'Nombre, apellido y RUT son obligatorios']);
            return;
        }

        // Cambiar contraseña si viene clave nueva
        if (!empty(trim($data['usr_claveNueva'] ?? ''))) {
            // Primero verificar clave antigua
            if (!empty($data['usr_claveAntigua'])) {
                $usuarioActual = $this->RespoModel->getUsuarioPorId($id);

                if (!$usuarioActual) {
                    echo json_encode(['error' => 'Usuario no encontrado']);
                    return;
                }

                if (!password_verify($data['usr_claveAntigua'], $usuarioActual->usr_clave)) {
                    echo json_encode(['error' => 'La clave antigua no es correcta']);
                    return;
                }
            }

            // Si pasa verificación, encriptar la nueva
            $clavePlano = trim($data['usr_claveNueva']);
            $userData['usr_clave'] = password_hash($clavePlano, PASSWORD_BCRYPT);
        } else {
            error_log("Clave no recibida o vacía");
        }

        // Guardar cambios
        if ($this->RespoModel->actualizaUsuario($id, $userData)) {
            echo json_encode([
                'status' => 'updated',
                'message' => "Se actualizó el usuario",
                'usr_clave_hash' => $userData['usr_clave'] ?? null
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo actualizar el usuario'
            ]);
        }
    }
    public function ActualizarUsuario($id) {
        $raw = file_get_contents('php://input');
        error_log("RAW INPUT: " . $raw);
        $data = json_decode($raw, true);
        error_log("Datos recibidos: " . print_r($data, true));
        if (!$data) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }
        if (!is_numeric($id)) {
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        $userData = [
            'usr_nombre'      => $data['usr_nombre']     ?? null,
            'usr_apellido'    => $data['usr_apellido']   ?? null,
            'usr_rut'         => $data['usr_rut']        ?? null,
            'usr_correo'      => $data['usr_correo']     ?? null,
            'usr_telefono'    => $data['usr_telefono']   ?? null,
            'usr_id_tipos'    => $data['usr_id_tipos']   ?? 2,
            'usr_id_sucursal' => $data['usr_id_sucursal']?? null,
            'usr_estado'      => $data['usr_estado']     ?? 'activo',
        ];
        if (empty($userData['usr_nombre']) || empty($userData['usr_apellido']) || empty($userData['usr_rut'])) {
            echo json_encode(['error' => 'Nombre, apellido y RUT son obligatorios']);
            return;
        }
        if (!empty(trim($data['usr_clave'] ?? ''))) {
            $clavePlano = trim($data['usr_clave']);
            $userData['usr_clave'] = password_hash($clavePlano, PASSWORD_BCRYPT);
        } else {
            error_log("Clave no recibida o vacía");
        }
        if ($this->RespoModel->actualizaUsuario($id, $userData)) {
            echo json_encode([
                'status' => 'updated',
                'message' => "Se actualizó el usuario",
                'usr_clave_hash' => $userData['usr_clave'] ?? null
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo actualizar el usuario'
            ]);
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
                'usr_id_tipos'      => isset($user['user_id_tipos']) ? 1 : 2,
            ];
            // Incluir clave si es responsable
            if (!empty(trim($data['usr_clave'] ?? ''))) {
                $clavePlano = trim($data['usr_clave']);
                $userData['usr_clave'] = password_hash($clavePlano, PASSWORD_BCRYPT);
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
        $listarE = $this->RespoModel->listaE();
        echo json_encode($listarE);
    }
    public function listaS() {
        $listarS = $this->RespoModel->listaS();
        echo json_encode($listarS);
    }
    public function listaT() {
        $listarT = $this->RespoModel->listaT();
        echo json_encode($listarT);
    }
        
}
