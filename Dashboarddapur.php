<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboarddapur extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->library('session');
    $this->load->database();
    $this->load->model('Dapur_model', 'dapur');

    date_default_timezone_set('Asia/Jakarta');
    if (!$this->session->userdata('id_admin')) {
      redirect('login');
    }

    if ($this->session->userdata('role') !== 'dapur') {
      show_error('Akses ditolak. Halaman ini khusus dapur.', 403);
    }
  }

  public function index()
  {
    $hidden_ids = $this->session->userdata('dapur_hidden_ids');
    if (!is_array($hidden_ids)) $hidden_ids = [];

    $data['count_diproses']   = $this->dapur->count_by_status('diproses');
    $data['count_siap_ambil'] = $this->dapur->count_by_status('siap_ambil');
    $data['pesanan']          = $this->dapur->get_pesanan_dapur($hidden_ids);

    $this->load->view('header');
    $this->load->view('dapur/sidebardapur');
    $this->load->view('topnav');
    $this->load->view('dapur/dashboarddapur', $data);
    $this->load->view('footer');
  }

  public function siap_ambil($id_pesanan)
{
  $id = (int)$id_pesanan;
  if ($id <= 0) show_404();

  $ok = $this->dapur->update_status($id, 'siap_ambil');
  if ($ok) {
    $this->dapur->log_status($id, 'siap_ambil');
  }

  redirect('dapur/dashboarddapur');
}

  public function selesai($id_pesanan)
  {
    $id = (int)$id_pesanan;
    if ($id <= 0) show_404();

    $hidden_ids = $this->session->userdata('dapur_hidden_ids');
    if (!is_array($hidden_ids)) $hidden_ids = [];

    if (!in_array($id, $hidden_ids, true)) {
      $hidden_ids[] = $id;
      $this->session->set_userdata('dapur_hidden_ids', $hidden_ids);
    }

    redirect('dapur/dashboarddapur');
  }

  public function reset_hidden()
  {
    $this->session->unset_userdata('dapur_hidden_ids');
    redirect('dapur/dashboarddapur');
  }
}
