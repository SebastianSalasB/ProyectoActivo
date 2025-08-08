<?php
class ActivosModel extends CI_Model {

    private $tableActivos = 'activos.activos';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function obtenerSistemaOperativo() {
        $this->db->select('
            s.*,
        ');
        $this->db->from('activos.sistemas_operativos s');
        $this->db->order_by('s.sio_id');
        $query = $this->db->get();
        return $query->result();
    }
    public function obtenerActivos() {
        $this->db->select('
            a.*,
            u.usr_id AS usuario_id,          
            u.usr_nombre AS nombre_usuario,
            u.usr_apellido AS apellido_usuario,
            t.tip_descripcion AS nombre_tipo,
            s.suc_nombre AS nombre_sucursal,
            e.emp_nombre AS nombre_empresa,
            d.dip_ip AS dip_ip
        ');
        $this->db->from('activos.activos a');
        $this->db->join('activos.usuarios u', 'u.usr_id = a.act_id_usuario');
        $this->db->join('activos.tiposactivos t', 't.tip_id = a.act_id_tipo');
        $this->db->join('activos.sucursales s', 's.suc_id = a.act_id_sucursal');
        $this->db->join('activos.empresas e', 'e.emp_id = s.suc_id_empresa');
        $this->db->join('activos.direccionesip d', 'd.dip_id_activo = a.act_id', 'left'); 
        
        $this->db->where('e.emp_estado','activo');
        $this->db->order_by('a.act_id');
        $query = $this->db->get();
        return $query->result();
    }
    public function filtrar_activos($buscador, $filtros) {
        $this->db->select('
            a.*,
            u.usr_id AS usuario_id,          
            u.usr_nombre AS nombre_usuario,
            u.usr_apellido AS apellido_usuario,
            t.tip_descripcion AS nombre_tipo,
            s.suc_nombre AS nombre_sucursal,
            e.emp_nombre AS nombre_empresa
        ');
        $this->db->from('activos.activos a');
        $this->db->join('activos.usuarios u', 'u.usr_id = a.act_id_usuario', 'left');
        $this->db->join('activos.tiposactivos t', 't.tip_id = a.act_id_tipo', 'left');
        $this->db->join('activos.sucursales s', 's.suc_id = a.act_id_sucursal', 'left');
        $this->db->join('activos.empresas e', 'e.emp_id = s.suc_id_empresa', 'left');
        $this->db->join('activos.direccionesip d', 'd.dip_id_activo = a.act_id', 'left');
        $this->db->where('e.emp_estado', 'activo');
        $this->db->order_by('a.act_id');

        // Filtro por texto de búsqueda
        if (!empty($buscador)) {
            $this->db->like('a.act_nombre', $buscador);
        }

        if (!empty($filtros['sucursales'])) {
            $this->db->where_in('a.act_id_sucursal', $filtros['sucursales']);
        }

        if (!empty($filtros['usuarios'])) {
            $this->db->where_in('u.usr_id', $filtros['usuarios']);
        }

        if (!empty($filtros['estados'])) {
            $this->db->where_in('a.act_estado', $filtros['estados']);
        }

        if (!empty($filtros['Tipos'])) {
            $this->db->where_in('a.act_id_tipo', $filtros['Tipos']);
        }

        $query = $this->db->get();
        return $query->result(); // OBJETOS, no arrays
    }


    public function contarActivos(){
        $this->db->from('activos.activos');
        $this->db->where('act_estado', 'activo'); // Solo cuenta los activos
        return $this->db->count_all_results();
    }
     public function activosPaginados($limit, $offset) {
        $this->db->select('
            a.*,
            u.usr_id AS usuario_id,          
            u.usr_nombre AS nombre_usuario,
            u.usr_apellido AS apellido_usuario,
            t.tip_descripcion AS nombre_tipo,
            s.suc_nombre AS nombre_sucursal,
            e.emp_nombre AS nombre_empresa,
            d.dip_ip AS dip_ip
        ');

        $this->db->from('activos.activos a');
        $this->db->join('activos.usuarios u', 'u.usr_id = a.act_id_usuario');
        $this->db->join('activos.tiposactivos t', 't.tip_id = a.act_id_tipo');
        $this->db->join('activos.sucursales s', 's.suc_id = a.act_id_sucursal');
        $this->db->join('activos.empresas e', 'e.emp_id = s.suc_id_empresa');
        $this->db->join('activos.direccionesip d', 'd.dip_id_activo = a.act_id', 'left'); // <-- JOIN con tabla de IPs

        $this->db->where('e.emp_estado','activo');
        $this->db->order_by('a.act_id');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }
    public function Mostrar_Activos($id) {
        return $this->db->get_where($this->tableActivos, ['act_id' => $id])->row_array();
    }
    public function insertarActivo($datos) {
        return $this->db->insert($this->tableActivos, $datos);
    }
    public function insertarComputador($datos) {
        return $this->db->insert('activos.computadores', $datos);
    }
    public function insertarServidor($datos) {
        return $this->db->insert('activos.servidores', $datos);
    }   
    public function ActualizaActivo($id, $datos) {
        $this->db->where('act_id', $id);
        return $this->db->update('activos.activos', $datos);
    }
    public function ActualizaServidor($id, $datos) {
        $this->db->where('ser_id_activo', $id);
        return $this->db->update('activos.servidores', $datos);
    }
    public function InsertarComputadora($datos) {
        return $this->db->insert($this->table, $datos);
    }
    public function ActualizaComputador($id, $datos) {
        $this->db->where('com_id_activo', $id);
        return $this->db->update('activos.computadores', $datos);
    }   
    public function obtenerIp() {
        $this->db->select('*');
        $this->db->from('activos.direccionesip');
        $query = $this->db->get();
        return $query->result();
    }
    public function insertarIP($datos) {
        return $this->db->insert('activos.direccionesip', $datos);
    } 
    public function eliminarIpActivo($id_activo) {
        return $this->db
            ->where('dip_id_activo', $id_activo)
            ->delete('activos.direccionesip');
    }  
    public function eliminarActivo($id) {
        $this->db->where('act_id', $id);
        return $this->db->update('activos.activos', ['act_estado' => 'eliminado']);    
    }
    public function activarActivo($id) {
        if (!$id) {
            return false;
        }

        $this->db->where('act_id', $id);
        return $this->db->update('activos.activos', ['act_estado' => 'activo']);
    }
    public function registrarBaja($datos) {
        $this->db->trans_start();

        $this->db->insert('activos.bajas', [
            'bjs_id_activo' => $datos['bjs_id_activo'],
            'bjs_id_usuario' => $datos['bjs_id_usuario'],
            'bjs_descripcion' => $datos['bjs_descripcion']
        ]);

        $this->db->where('act_id', $datos['bjs_id_activo']);
        $this->db->update('activos.activos', ['act_estado' => 'baja']);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
    public function listaTipo() {
        return $this->db->get('activos.activos')->result();
    }
    public function listaActivos() {
        return $this->db->get('activos.activos')->result();
    }
    public function listEmpresa() {
        return $this->db->get('activos.empresas')->result();
    }
    public function listaSucursal() {
        return $this->db->get('activos.sucursales')->result();
    }
    public function obtenerTipos() {
        return $this->db->get('activos.tiposactivos')->result();
    }
    public function obtenerUsuariosConSucursal(){
        $this->db->select('*');
        $this->db->from('activos.empresas');
        $this->db->join('activos.sucursales', 'emp_id = suc_id_empresa');
        $query = $this->db->get();

        $resultado = $query->result(); 
        
        echo json_encode($resultado);
    }



}
