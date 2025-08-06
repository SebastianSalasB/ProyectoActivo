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
            'man_fecha'      => $data['man_fecha']
        ]);
        // Actualizar estado del activo
        $this->db->where('act_id', $data['act_id']);
        $this->db->update('activos.activos', ['act_estado' => 'mantenimiento']);
        // Finalizar transacción
        $this->db->trans_complete();
        // Devolver true si todo fue bien, false si hubo error
        return $this->db->trans_status();
    }
}
