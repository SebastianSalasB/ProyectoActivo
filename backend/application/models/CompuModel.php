<?php
class CompuModel extends CI_Model {

    protected $table = 'activos.computadores';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function obtenerComputador() {
        return $this->db->get($this->table)->result();
    }
    public function insertarComputador($data) {
        return $this->db->insert($this->table, $data);
    }
    public function ActualizaComputador($id, $data) {
        $this->db->where('com_id', $id);
        return $this->db->update($this->table, $data);
    }
}