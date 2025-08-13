<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Protected_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        if(!$this->session->userdata('user')){
                if ($usuarios) {
                return $this->output
                    ->set_status_header(200)
                    ->set_output(json_encode([
                        'status' => 'success',
                        'user' => $usuarios
                    ]));
            } else {
                return $this->output
                    ->set_status_header(401)
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'No hay sesión activa.'
                    ]));
                exit();
            }
        }
    }
}
