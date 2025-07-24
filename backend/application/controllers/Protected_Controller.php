<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Protected_Controller extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('user')) {
           
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Acceso no autorizado. Debe iniciar sesión.'
                ]));
            exit;
        }
    }
}
