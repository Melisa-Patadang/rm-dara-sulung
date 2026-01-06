<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->library('session');
    $this->load->helper('url');
  }

  public function logout() {
    $this->session->sess_destroy();
    $this->session->set_flashdata('pesan_sukses', 'Anda berhasil logout.');
    redirect('login'); 
  }
}
