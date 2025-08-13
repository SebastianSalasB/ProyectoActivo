<?php
class TipoActivoModel extends CI_Model {

    protected $table = 'activos.tiposactivos';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function obtenerTipos() {
        $this->db->where('tip_estado', 'activo');
        
        $this->db->order_by('tip_id');
        $query = $this->db->get('activos.tiposactivos'); 
        return $query->result();
    }
   public function insertarTipos($data){
        return $this->db->insert('activos.tiposactivos', $data);
    }
    public function ActualizarTipos($id, $data) {
        $this->db->where('tip_id', $id);
        return $this->db->update($this->table, $data);
    }
     public function eliminarTipo($id) {
        $this->db->where('tip_id', $id);
        return $this->db->update('activos.tiposactivos', ['tip_estado' => 'eliminado']);
    }
}
