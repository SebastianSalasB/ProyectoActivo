<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MantencionModel extends CI_Model {

    public function Mantencion($data) {
       
        $this->db->trans_start();
        
        $this->db->insert('activos.mantenciones', [
            'man_id_activo'  => $data['man_id_activo'],
            'man_id_usuario' => $data['man_id_usuario'],
            'man_fecha'      => $data['man_fecha'],
            'man_descripcion'      => $data['man_descripcion']
        ]);
        
        $this->db->where('act_id', $data['man_id_activo']);
        $this->db->update('activos.activos', ['act_estado' => 'mantenimiento']);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    public function MantencionFechaSalida($data) {
        if (!isset($data['man_id_activo']) || !isset($data['man_fecha_salida'])) {
            return false;
        }
        $this->db->where('man_id_activo', $data['man_id_activo']);
        $this->db->order_by('man_id', 'DESC'); 
        $this->db->limit(1);
        $ultimo = $this->db->get('activos.mantenciones')->row();

        if (!$ultimo) {
            return false; 
        }
        $this->db->where('man_id', $ultimo->man_id);
        return $this->db->update('activos.mantenciones', ['man_fecha_salida' => $data['man_fecha_salida']]);
    }
    
    public function obtenerMantencion($id) {
        $this->db->select('
            m.*,
            u.usr_nombre AS nombre_usuario,
        ');
        $this->db->from('activos.mantenciones m');


        $this->db->where('m.man_id_activo', $id);

        $this->db->join('activos.usuarios u', 'u.usr_id = m.man_id_usuario', 'left');
        $this->db->order_by('m.man_id');
        $query = $this->db->get();
        return $query->result();
    }
}
