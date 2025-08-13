<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MantencionModel extends CI_Model {

    public function Mantencion($data) {
        // Iniciar una transacción
        $this->db->trans_start();
        // Insertar mantención
        $this->db->insert('activos.mantenciones', [
            'man_id_activo'  => $data['man_id_activo'],
            'man_id_usuario' => $data['man_id_usuario'],
            'man_fecha'      => $data['man_fecha'],
            'man_descripcion'      => $data['man_descripcion']
        ]);
        // Actualizar estado del activo
        $this->db->where('act_id', $data['man_id_activo']);
        $this->db->update('activos.activos', ['act_estado' => 'mantenimiento']);
        // Finalizar transacción
        $this->db->trans_complete();
        // Devolver true si todo fue bien, false si hubo error
        return $this->db->trans_status();
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
