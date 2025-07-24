<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:3000') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Content-Type: application/json");
#[\AllowDynamicProperties]
class EmpresaR extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('EmpresaModel');
        $this->load->model('RespoModel');  
    }
    public function index() {
        $empresas = $this->EmpresaModel->EmpresaSucursales();
        echo json_encode($empresas);
        
    }
    public function listU() {
        $usuarios = $this->RespoModel->listaU();
        echo json_encode($usuarios);
    }
    public function listS() {
        $sucursales = $this->RespoModel->listaS();
        echo json_encode($sucursales);
    }
    public function listT() {
        $tipos = $this->EmpresaModel->optenerTipos();
        echo json_encode($tipos);
    }
    public function CrearEmpresa() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }
        if (empty($data['emp_nombre']) || empty($data['emp_direccion'])) {
            echo json_encode(['error' => 'Nombre y dirección son obligatorios']);
            return;
        }
        if (!isset($data['sucursales']) || !is_array($data['sucursales']) || count($data['sucursales']) === 0) {
            echo json_encode(['error' => 'Debe agregar al menos una sucursal']);
            return;
        }
        foreach ($data['sucursales'] as $sucursal) {
            if (empty($sucursal['suc_nombre']) || empty($sucursal['suc_direccion'])) {
                echo json_encode(['error' => 'Cada sucursal debe tener nombre y dirección']);
                return;
            }
        }
        // Iniciar transacción
        $this->db->trans_begin();
        $empresaData = [
            'emp_nombre' => $data['emp_nombre'],
            'emp_direccion' => $data['emp_direccion']
        ];
        $insertEmpresa = $this->EmpresaModel->insertarEmpresa($empresaData);
        if ($insertEmpresa) {
            // Obtener el ID de la empresa insertada
            $emp_id = $this->db->query("SELECT currval(pg_get_serial_sequence('activos.empresas', 'emp_id')) AS id")->row()->id;

            foreach ($data['sucursales'] as $sucursal) {
                $sucursalData = [
                    'suc_nombre' => $sucursal['suc_nombre'],
                    'suc_direccion' => $sucursal['suc_direccion'],
                    'suc_id_empresa' => $emp_id
                ];

                $insertSucursal = $this->EmpresaModel->insertarSucursal($sucursalData);
                if (!$insertSucursal) {
                    $this->db->trans_rollback();
                    log_message('error', 'Error al insertar sucursal: ' . print_r($sucursalData, true));
                    echo json_encode(['error' => 'Error al insertar sucursal']);
                    return;
                }
            }

            // Confirmar transacción
            $this->db->trans_commit();
            echo json_encode(['message' => 'Empresa y sucursales registradas correctamente']);
        } else {
            $this->db->trans_rollback();
            echo json_encode(['error' => 'Error al registrar empresa']);
        }
    }
    public function ActializarEmpresa($id){
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!$datos) {
            echo json_encode(['error' => 'No se recibieron datos válidos']);
            return;
        }

        // Actualizar datos de la empresa
        $datosEmpresas = [
            'emp_nombre' => $datos['emp_nombre'] ?? null,
            'emp_direccion' => $datos['emp_direccion'] ?? null
        ];

        $this->load->model('EmpresaModel');

        // Iniciar transacción por seguridad
        $this->db->trans_start();

        $empresaActualizada = $this->EmpresaModel->ActualizaEmpresa($id, $datosEmpresas);

        // Procesar sucursales si vienen en los datos
        if (isset($datos['sucursales']) && is_array($datos['sucursales'])) {
            foreach ($datos['sucursales'] as $sucursal) {
                $datosSucursal = [
                    'suc_nombre' => $sucursal['suc_nombre'],
                    'suc_direccion' => $sucursal['suc_direccion'],
                    'suc_id_empresa' => $id
                ];

                if (isset($sucursal['suc_id']) && $sucursal['suc_id']) {
                    // Actualizar sucursal existente
                    $this->EmpresaModel->ActualizarSucursal($sucursal['suc_id'], $datosSucursal);
                } else {
                    // Crear nueva sucursal
                    $this->EmpresaModel->CrearSucursal($datosSucursal);
                }
            }
        } else {
            // Si no hay sucursales enviadas, crear una por defecto con los datos de la empresa
            $this->EmpresaModel->CrearSucursal([
                'suc_nombre' => $datos['emp_nombre'] . ' - Sucursal Principal',
                'suc_direccion' => $datos['emp_direccion'],
                'suc_id_empresa' => $id
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() && $empresaActualizada) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    public function Eliminar($id) {
        $result = $this->EmpresaModel->eliminarEmpresa();
        if ($result) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    } 
    public function eliminarSucursal($id) {
        $deleted = $this->EmpresaModel->eliminarSucursal();
        if ($deleted) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
     
}
