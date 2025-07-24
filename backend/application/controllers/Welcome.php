<?php
defined('BASEPATH') OR exit('No direct script access allowed');
#[\AllowDynamicProperties]
class Welcome extends CI_Controller {
	
	public function index()
	{
		$this->load->view('welcome_message');
		if (!$this->session->userdata('user')) {
			redirect('auth/login'); 
		}
	}
}
