<?php
class DireccionIpModel extends CI_Model {
    public function insertarIP($data) {
        return $this->db->insert('activos.direccionesip', $data);
    }
    public function eliminarIpActivo($id_activo) {
        return $this->db
            ->get_where('activos.direccionesip', ['dip_id_activo' => $id_activo])
            ->result();  
    }

}

