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
class Activos extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('ActivosModel');   
        $this->load->model('CompuModel');
        $this->load->model('ServerModel');
        $this->load->model('DireccionIpModel');
        header('Content-Type: application/json');
    }
    public function index(){
        // Establecer encabezado JSON
        header('Content-Type: application/json');
        // Validar método
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        // Obtener datos
        $activos = $this->ActivosModel->obtenerActivos();
        $contarActivo = $this->ActivosModel->contarActivos();

        $computadores = $this->CompuModel->obtenerComputador();
        $servidores = $this->ServerModel->obtenerServidor();
        $ips = $this->ActivosModel->obtenerIp();
        // Mapear datos por ID
        $MapaComputadores = [];
        foreach ($computadores as $com) {
            $MapaComputadores[$com->com_id_activo] = $com;
        }
        $MapaServidores = [];
        foreach ($servidores as $ser) {
            $MapaServidores[$ser->ser_id_activo] = $ser;
        }
        $mapaIps = [];
        foreach ($ips as $ip) {
            $mapaIps[$ip->dip_id_activo][] = $ip->dip_ip;
        }
        $resultado = [];
        foreach ($activos as $act) {
            $fila = [
                'act_id' => $act->act_id,
                'act_fecha_registro' => $act->act_fecha_registro,
                'act_marca' => $act->act_marca,
                'act_modelo' => $act->act_modelo,
                'act_fabricante' => $act->act_fabricante,
                'act_numero_serie' => $act->act_numero_serie,
                'act_ubicacion' => $act->act_ubicacion,
                'act_id_usuario' => $act->act_id_usuario,
                'act_id_sucursal' => $act->act_id_sucursal,
                'act_id_tipo' => $act->act_id_tipo,
                'act_descripcion' => $act->act_descripcion,
                'act_factura' => $act->act_factura,
                'act_estado' => $act->act_estado,
                // JOINs
                
                'nombre_usuario' => $act->nombre_usuario,
                'apellido_usuario' => $act->apellido_usuario,
                'nombre_tipo' => $act->nombre_tipo,
                'nombre_sucursal' => $act->nombre_sucursal,
                'nombre_empresa' => $act->nombre_empresa,
                // Inicializa vacíos
                'tipo_equipo' => null,
                'datos_computador' => new stdClass(),
                'datos_servidor' => new stdClass(),
                'direcciones_ip' => [], 
            ];
            // Datos específicos
            if (isset($MapaComputadores[$act->act_id])) {
                $fila['tipo_equipo'] = 'computador';
                $fila['datos_computador'] = $MapaComputadores[$act->act_id];
            }
            if (isset($MapaServidores[$act->act_id])) {
                $fila['tipo_equipo'] = 'servidor';
                $fila['datos_servidor'] = $MapaServidores[$act->act_id];
            }
            if (isset($mapaIps[$act->act_id])) {
                $fila['direcciones_ip'] = $mapaIps[$act->act_id];
            }
            $resultado[] = $fila;
        }
        echo json_encode($resultado);
    }
    public function filtros() {
        // Desactivar reportes innecesarios
        error_reporting(0); // Esto es para pruebas; luego puedes configurarlo mejor
        ini_set('display_errors', 0);

        // Leer entrada como JSON
        $json = file_get_contents('php://input');
        $input = json_decode($json, true);

       

        $buscador = $input['buscador'];
        $filtros = $input['filtros'];

        // Cargar modelo
        $activos = $this->ActivosModel->filtrar_activos($buscador, $filtros);
        $computadores = $this->CompuModel->obtenerComputador();
        $servidores = $this->ServerModel->obtenerServidor();
        $ips = $this->ActivosModel->obtenerIp();
        // Mapear datos por ID
        $MapaComputadores = [];
        foreach ($computadores as $com) {
            $MapaComputadores[$com->com_id_activo] = $com;
        }
        $MapaServidores = [];
        foreach ($servidores as $ser) {
            $MapaServidores[$ser->ser_id_activo] = $ser;
        }
        $mapaIps = [];
        foreach ($ips as $ip) {
            $mapaIps[$ip->dip_id_activo][] = $ip->dip_ip;
        }
        $resultado = [];
        foreach ($activos as $act) {
            $fila = [
                'act_id' => $act->act_id,
                'act_fecha_registro' => $act->act_fecha_registro,
                'act_marca' => $act->act_marca,
                'act_modelo' => $act->act_modelo,
                'act_fabricante' => $act->act_fabricante,
                'act_numero_serie' => $act->act_numero_serie,
                'act_ubicacion' => $act->act_ubicacion,
                'act_id_usuario' => $act->act_id_usuario,
                'act_id_sucursal' => $act->act_id_sucursal,
                'act_id_tipo' => $act->act_id_tipo,
                'act_descripcion' => $act->act_descripcion,
                'act_factura' => $act->act_factura,
                'act_estado' => $act->act_estado,
                // JOINs
                
                'nombre_usuario' => $act->nombre_usuario,
                'apellido_usuario' => $act->apellido_usuario,
                'nombre_tipo' => $act->nombre_tipo,
                'nombre_sucursal' => $act->nombre_sucursal,
                'nombre_empresa' => $act->nombre_empresa,
                // Inicializa vacíos
                'tipo_equipo' => null,
                'datos_computador' => new stdClass(),
                'datos_servidor' => new stdClass(),
                'direcciones_ip' => [], 
            ];
            // Datos específicos
            if (isset($MapaComputadores[$act->act_id])) {
                $fila['tipo_equipo'] = 'computador';
                $fila['datos_computador'] = $MapaComputadores[$act->act_id];
            }
            if (isset($MapaServidores[$act->act_id])) {
                $fila['tipo_equipo'] = 'servidor';
                $fila['datos_servidor'] = $MapaServidores[$act->act_id];
            }
            if (isset($mapaIps[$act->act_id])) {
                $fila['direcciones_ip'] = $mapaIps[$act->act_id];
            }
            $resultado[] = $fila;
        }
        echo json_encode($resultado);
    }

    public function contarActivos($pagina = 1) {
        $limit = 8;
        $offset = ($pagina - 1) * $limit;

        $activos = $this->ActivosModel->activosPaginados($limit, $offset);
        $contarActivo = $this->ActivosModel->contarActivos(); // Cuenta total de registros
        $computadores = $this->CompuModel->obtenerComputador();
        $servidores = $this->ServerModel->obtenerServidor();
        $ips = $this->ActivosModel->obtenerIp();
        // Mapear datos por ID
        $MapaComputadores = [];
        foreach ($computadores as $com) {
            $MapaComputadores[$com->com_id_activo] = $com;
        }
        $MapaServidores = [];
        foreach ($servidores as $ser) {
            $MapaServidores[$ser->ser_id_activo] = $ser;
        }
        $mapaIps = [];
        foreach ($ips as $ip) {
            $mapaIps[$ip->dip_id_activo][] = $ip->dip_ip;
        }
        $resultado = [];
        foreach ($activos as $act) {
            $fila = [
                'act_id' => $act->act_id,
                'act_fecha_registro' => $act->act_fecha_registro,
                'act_marca' => $act->act_marca,
                'act_modelo' => $act->act_modelo,
                'act_fabricante' => $act->act_fabricante,
                'act_numero_serie' => $act->act_numero_serie,
                'act_ubicacion' => $act->act_ubicacion,
                'act_id_usuario' => $act->act_id_usuario,
                'act_id_sucursal' => $act->act_id_sucursal,
                'act_id_tipo' => $act->act_id_tipo,
                'act_descripcion' => $act->act_descripcion,
                'act_factura' => $act->act_factura,
                'act_estado' => $act->act_estado,
                // JOINs
                
                'nombre_usuario' => $act->nombre_usuario,
                'apellido_usuario' => $act->apellido_usuario,
                'nombre_tipo' => $act->nombre_tipo,
                'nombre_sucursal' => $act->nombre_sucursal,
                'nombre_empresa' => $act->nombre_empresa,
                // Inicializa vacíos
                'tipo_equipo' => null,
                'datos_computador' => new stdClass(),
                'datos_servidor' => new stdClass(),
                'direcciones_ip' => [], 
            ];
            // Datos específicos
            if (isset($MapaComputadores[$act->act_id])) {
                $fila['tipo_equipo'] = 'computador';
                $fila['datos_computador'] = $MapaComputadores[$act->act_id];
            }
            if (isset($MapaServidores[$act->act_id])) {
                $fila['tipo_equipo'] = 'servidor';
                $fila['datos_servidor'] = $MapaServidores[$act->act_id];
            }
            if (isset($mapaIps[$act->act_id])) {
                $fila['direcciones_ip'] = $mapaIps[$act->act_id];
            }
            $resultado[] = $fila;
        }
        echo json_encode([
            'Activos' => $resultado,
            'total' => $contarActivo
        ]);
        
    }
    public function CrearActivo() {
        $datos = json_decode(file_get_contents("php://input"), true);

        if (!$datos || !isset($datos['activos'])) {
            echo json_encode(['status' => 'error', 'message' => 'Datos no válidos']);
            return;
        }
        $this->db->trans_start(); // Transacción segura

        foreach ($datos['activos'] as $activo) {
            // Preparar solo los datos del activo
            $activosDatos = [
                'act_id_usuario'     => $activo['usuario_id'],
                'act_id_sucursal'    => $activo['sucursal_id'],
                'act_descripcion'    => $activo['descripcion'],
                'act_fecha_registro' => $activo['fecharegistro'],
                'act_modelo'         => $activo['modelo'],
                'act_marca'          => $activo['marca'],
                'act_factura'        => $activo['factura'],
                'act_ubicacion'      => $activo['direccion'],
                'act_id_tipo'        => $activo['tipoactivo'],
                'act_numero_serie'   => $activo['numeroserie'],
                'act_fabricante'     => $activo['fabricante']
            ];
           
            $act_id = $this->ActivosModel->insertarActivo($activosDatos);            
            // Insertar IPs si existen
           $act_id = $this->db->insert_id(); // Esto debe ir inmediatamente después

            // Insertar IPs si existen
            if ($act_id && !empty($activo['ips']) && is_array($activo['ips'])) {
                foreach ($activo['ips'] as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        $this->ActivosModel->insertarIP([
                            'dip_ip' => $ip,
                            'dip_id_activo' => $act_id
                        ]);
                    }
                }
            }
            $act_id = $this->db->insert_id(); 

            // Datos de computadora
            $ComputadoresDatos = [
                'com_ram'               => $activo['com_ram'],
                'com_cpu'               => $activo['com_cpu'],
                'com_disco'             => $activo['com_disco'],
                'com_sistema_operativo' => $activo['com_sistema_operativo'],
                'com_id_activo'         => $act_id
            ];

            // Solo insertar si alguno de los campos de Computadores Datos no está vacío
            if (
                !empty($ComputadoresDatos['com_ram']) || !empty($ComputadoresDatos['com_cpu']) || 
                !empty($ComputadoresDatos['com_disco']) || !empty($ComputadoresDatos['com_sistema_operativo'])
                ) {
                    $this->ActivosModel->insertarComputador($ComputadoresDatos);
            }

            // Datos de servidor
            $ServidorDatos = [
                'ser_ram'               => $activo['ser_ram'],
                'ser_cpu'               => $activo['ser_cpu'],
                'ser_disco'             => $activo['ser_disco'],
                'ser_sistema_operativo' => $activo['ser_sistema_operativo'],
                'ser_cantidad_max_cpu'  => $activo['ser_cantidad_max_cpu'],
                'ser_ranuras_ram'       => $activo['ser_ranuras_ram'],
                'ser_nombre'            => $activo['ser_nombre'],
                'ser_id_activo'         => $act_id
            ];
            
            // Solo insertar si alguno de los campos de ServidorDatos no está vacío
            if (
                !empty($ServidorDatos['ser_ram']) || !empty($ServidorDatos['ser_cpu']) || !empty($ServidorDatos['ser_disco']) ||
                !empty($ServidorDatos['ser_sistema_operativo']) || !empty($ServidorDatos['ser_cantidad_max_cpu']) ||
                !empty($ServidorDatos['ser_ranuras_ram']) || !empty($ServidorDatos['ser_nombre'])
            ) {
                $this->ActivosModel->insertarServidor($ServidorDatos);
            }
            
        }
        $this->db->trans_complete();

        if ($this->db->trans_status()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en la transacción']);
        }
    }
    public function ActualizarActivo($id) {
        $datos = json_decode(file_get_contents('php://input'), true); 
        if (!$datos) {
            echo json_encode(['error' => 'No se recibieron datos válidos', $datos]);
            return;
        }
        // 1. Actualizar datos generales del activo
        $DatosActivos = [
            'act_id_usuario'     => $datos['act_id_usuario'] ?? null,
            'act_id_sucursal'    => $datos['act_id_sucursal'] ?? null,
            'act_id_tipo'        => $datos['act_id_tipo'] ?? null,
            'act_descripcion'    => $datos['act_descripcion'] ?? null,
            'act_fecha_registro' => $datos['act_fecha_registro'] ?? null,
            'act_modelo'         => $datos['act_modelo'] ?? null,
            'act_marca'          => $datos['act_marca'] ?? null,
            'act_factura'        => $datos['act_factura'] ?? null,
            'act_ubicacion'      => $datos['act_ubicacion'] ?? null,
            'act_numero_serie'   => $datos['act_numero_serie'] ?? null,
            'act_fabricante'     => $datos['act_fabricante'] ?? null
        ];
        $activoActualiza = $this->ActivosModel->ActualizaActivo($id, $DatosActivos);
        // 2. Computador
        $computadoraLista = true;
        if (isset($datos['datos_computador'])) {
            $compuData = [
                'com_id_activo'         => $id,
                'com_ram'               => $datos['datos_computador']['com_ram'] ?? null,
                'com_cpu'               => $datos['datos_computador']['com_cpu'] ?? null,
                'com_disco'             => $datos['datos_computador']['com_disco'] ?? null,
                'com_sistema_operativo' => $datos['datos_computador']['com_sistema_operativo'] ?? null
            ];
            if (!empty($datos['datos_computador']['com_id'])) {
                // actualizar si existe ID
                $computadoraLista = $this->CompuModel->ActualizaComputador($datos['datos_computador']['com_id'], $compuData);
            } else {
                // crear si no existe
                $computadoraLista = $this->CompuModel->insertarComputador($compuData);
            }
        }
        // 3. Servidor
        $servidorLista = true;
        if (isset($datos['datos_servidor'])) {
            $servidorDatos = [
                'ser_id_activo'        => $id,
                'ser_ram'              => $datos['datos_servidor']['ser_ram'] ?? null,
                'ser_cpu'              => $datos['datos_servidor']['ser_cpu'] ?? null,
                'ser_disco'            => $datos['datos_servidor']['ser_disco'] ?? null,
                'ser_sistema_operativo'=> $datos['datos_servidor']['ser_sistema_operativo'] ?? null,
                'ser_cantidad_max_cpu' => $datos['datos_servidor']['ser_cantidad_max_cpu'] ?? null,
                'ser_ranuras_ram'      => $datos['datos_servidor']['ser_ranuras_ram'] ?? null,
                'ser_nombre'           => $datos['datos_servidor']['ser_nombre'] ?? null
            ];

            if (!empty($datos['datos_servidor']['ser_id'])) {
                $servidorLista = $this->ServerModel->ActualizaServidor($datos['datos_servidor']['ser_id'], $servidorDatos);
            } else {
                $servidorLista = $this->ServerModel->insertarServidor($servidorDatos);
            }
        }
        // 4. Direcciones IP (si las estás usando)
        $ipLista = true;
        if (!empty($datos['direcciones_ip']) && is_array($datos['direcciones_ip'])) {
            // Eliminar IPs antiguas
            
            $this->ActivosModel->eliminarIpActivo($id);
            // Insertar las nuevas
            foreach ($datos['direcciones_ip'] as $ip) {
                if (!empty($ip)) {
                    $this->ActivosModel->insertarIP(['dip_id_activo' => $id,'dip_ip' => $ip]);
                    
                }
                
            }
        }
        // Respuesta
        if ($activoActualiza && $computadoraLista && $servidorLista && $ipLista) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    public function Eliminar($id){
        // Ahora puedes usar $input['campo'] para acceder a los datos
        $Resultado = $this->ActivosModel->eliminarActivo($id);
        if ($Resultado) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    public function activar(){
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['act_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'ID faltante']);
            return;
        }

        $this->load->model('ActivosModel');
        $resultado = $this->ActivosModel->activarActivo($data['act_id']);

        if ($resultado) {
            echo json_encode(['status' => 'activo']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo activar']);
        }
    }
    public function obtenerUsuariosConSucursal(){
        $resultado =  $this->ActivosModel->ObtenerUsuariosSucursal($datos);
        echo json_encode($resultado);
    }
    public function darDeBaja() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (
            empty($data['bjs_id_activo']) || empty($data['bjs_id_usuario']) || empty($data['bjs_descripcion'])
        ) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->ActivosModel->registrarBaja($data);

        if ($result) {
            echo json_encode(['status' => 'baja']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }   
    public function listaTipo() {
        $tipo = $this->ActivosModel->obtenerTipos();

        echo json_encode($tipo);
    }
    public function listaActivos() {
        $activos = $this->ActivosModel->ListaActivos();

        echo json_encode($activos);
    }
    public function listaEmpresa() {
        $empresa = $this->ActivosModel->listEmpresa();

        echo json_encode($empresa);
    }
    public function listaSucursal() {
        $sucursal = $this->ActivosModel->listaSucursal();
        echo json_encode($sucursal);
    }
    public function listaUsuarios() {
        $usuarios = $this->db->get('activos.usuarios')->result();
        echo json_encode($usuarios);
    }
    public function listS() {
        $sucursal = $this->ActivosModel->listaSucursal();
        echo json_encode($sucursal);
    }
    public function listaTipos() {
        $tipos = $this->ActivosModel->obtenerTipos();
        echo json_encode($tipos);
    }
    
}
