<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:3000') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

#[\AllowDynamicProperties]
class TiposActivos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('TipoActivoModel'); 
    }
    public function index() { 
        $tipos = $this->TipoActivoModel->obtenerTipos();
        log_message('error', 'Datos recibidos: ' . print_r($tipos, true));
        echo json_encode($tipos);
    }
    // Crear un nuevo tipo de activo
    public function CrearTipo(){
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        $Datos = json_decode(file_get_contents("php://input"), true);
        // Validar si se recibieron datos
        if (!$Datos || empty($Datos['tip_descripcion'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se recibió tip_descripcion válida'
            ]);
            return;
        }
        // Preparar datos
        $tiposDatos = [
            'tip_descripcion' => $Datos['tip_descripcion'],
            'tip_estado' => 'activo'
        ];
        // Insertar en base de datos
        if ($this->TipoActivoModel->insertarTipos($tiposDatos)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Tipo de activo creado exitosamente',
                'tipo_id' => $this->db->insert_id()
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al insertar tipo de activo'
            ]);
        }
    }
    // Actualizar un tipo de activo por id
    public function ActualizarTipos($id) {
        $Datos = json_decode(file_get_contents('php://input'), true);
        if (!$Datos) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }
        $tiposDatos = [
            'tip_descripcion' => $Datos['tip_descripcion'] ?? null,
            // otros campos si existen
        ];
        if ($this->TipoActivoModel->ActualizarTipos($id, $tiposDatos)) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    // Eliminar un tipo de activo por id
    public function EliminarTipo($id) {
        $result = $this->TipoActivoModel->eliminarTipo($id);
        if ($result) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
}
