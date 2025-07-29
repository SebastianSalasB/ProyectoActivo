<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RespoModel extends CI_Model
{
    public function responsablesListado() {
        $this->db->select('u.*, s.suc_nombre as nombre_sucursal, t.tus_tipo as nombre_tipo');
        $this->db->from('activos.usuarios u');
        $this->db->join('activos.sucursales s', 's.suc_id = u.usr_id_sucursal', 'left');
        $this->db->join('activos.tipos_usuario t', 't.tus_id = u.usr_id_tipos', 'left');
        $this->db->where('u.usr_estado', 'activo');
        $this->db->order_by('u.usr_id');
        $query = $this->db->get();
        return $query->result();
    }
    public function contarResponsables(){
        $this->db->from('activos.usuarios');
        $this->db->where('usr_estado', 'activo'); // Solo cuenta los activos
        return $this->db->count_all_results();
    }
    public function insertarUsuario($data) {
        return $this->db->insert('activos.usuarios', $data);
    }
    public function actualizaUsuario($id,$data) {
        $this->db->where('usr_id', $id);
        return $this->db->update('activos.usuarios', $data);
    }
    public function eliminarUsuario($id) {
        $data = ['usr_estado' => 'eliminado']; 
        $this->db->where('usr_id', $id);       
        return $this->db->update('activos.usuarios', $data);
    }
    public function listaE() {
        return $this->db->get('activos.empresas')->result();
    }
    public function listaS() {
        return $this->db->get('activos.sucursales')->result();
    }
    public function listaT() {
        return $this->db->get('activos.tipos_usuario')->result();
    }
    public function listaU() {
        return $this->db->get('activos.usuarios')->result();
    }
    
}
