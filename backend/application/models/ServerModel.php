<?php
class ServerModel extends CI_Model {

    private $TableServer = 'activos.servidores';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function obtenerServidor() {
        return $this->db->get($this->TableServer)->result();
    }
    public function insertarServidor($data) {
        return $this->db->insert($this->TableServer, $data);
    }
    public function ActualizaServidor($id, $data) {
        $this->db->where('ser_id', $id);
        return $this->db->update($this->TableServer, $data);
    }
}
