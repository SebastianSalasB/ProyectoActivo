<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class IniciarSession extends CI_Model {

    protected $table = 'activos.usuarios'; 

    public function __construct() {
        parent::__construct();

    }
    public function VeridicacionDatos($rut, $password) {
        $this->db->where('usr_rut', $rut);
        $query = $this->db->get('activos.usuarios');
        $usuario = $query->row();

        if ($usuario && password_verify($password, $usuario->usr_clave)) {
            return $usuario;
        }
        return false;
    }
}