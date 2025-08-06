<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class IniciarSession extends CI_Model {

    protected $table = 'activos.usuarios'; 

    public function __construct() {
        parent::__construct();
    }
    public function VeridicacionDatos($rut, $password) {
        $this->db->where('usr_rut', $rut);
        
        $query = $this->db->get($this->table);

        $usuarios = $query->result();

        if (empty($usuarios)) {
            return false; // No hay usuarios con ese RUT
        }
        foreach ($usuarios as $usuario) {
            if (password_verify($password, $usuario->usr_clave)) {
                return $usuario; // Retorna el usuario que coincide
            }
        }
        return false; // Ninguna contraseña coincide
    }

}
