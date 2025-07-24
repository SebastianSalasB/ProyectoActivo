<?php
class EmpresaModel extends CI_Model {

    private $tableActivos = 'activos.empresas';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function insertarEmpresa($data) {
        return $this->db->insert($this->tableActivos, $data);
    }
    public function eliminarEmpresa($id) {
        $this->db->where('emp_id', $id);
        return $this->db->update('activos.empresas', ['emp_estado' => 'eliminado']);
    }
    public function eliminarSucursal($id) {
        $this->db->where('suc_id', $id);
        return $this->db->update('activos.sucursales', ['suc_estados' => 'eliminado']);
    }
    public function insertarSucursal($data) {
        return $this->db->insert('activos.sucursales', $data);
    }
    public function EmpresaSucursales() {
        $this->db->select('
            e.emp_id,
            e.emp_nombre,
            e.emp_direccion,
            s.suc_id,
            s.suc_nombre,
            s.suc_direccion
        ');
        $this->db->from('activos.empresas e');
        $this->db->join('activos.sucursales s', 's.suc_id_empresa = e.emp_id');

        $this->db->where('s.suc_estados', 'activo');
        $this->db->where('e.emp_estado', 'activo');
        

        $query = $this->db->get();
        $result = $query->result();

        // Agrupar sucursales dentro de cada empresa
        $empresas = [];

        foreach ($result as $row) {
            $emp_id = $row->emp_id;

            if (!isset($empresas[$emp_id])) {
                $empresas[$emp_id] = [
                    'emp_id' => $row->emp_id,
                    'emp_nombre' => $row->emp_nombre,
                    'emp_direccion' => $row->emp_direccion,
                    'sucursales' => []
                ];
            }

            $empresas[$emp_id]['sucursales'][] = [
                'suc_id' => $row->suc_id,
                'suc_nombre' => $row->suc_nombre,
                'suc_direccion' => $row->suc_direccion
            ];
        }

        return array_values($empresas); // Reindexa como array normal
    }
    public function optenerTipos() {
        return $this->db->get('activos.tiposactivos')->result();
    }
    public function ActualizaEmpresa($id, $data) {
        $this->db->where('emp_id', $id);
        return $this->db->update('activos.empresas', $data);
    }
    public function ActualizarSucursal($id, $data) {
        $this->db->where('suc_id', $id);
        return $this->db->update('activos.sucursales', $data);
    }
    public function CrearSucursal($data) {
        return $this->db->insert('activos.sucursales', $data);
    }

}
