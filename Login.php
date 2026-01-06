<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {     

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');  
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('Madmin'); 
    }

    public function index()
    {
        
        $data['roles'] = $this->Madmin->get_role();
        $this->load->view('login', $data);
    }

    public function proses()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $role     = $this->input->post('role');

        if (empty($username) || empty($password) || empty($role)) {
            $this->session->set_flashdata("pesan_gagal", "Semua field wajib diisi!");
            redirect('login');
        }

        $cek = $this->Madmin->cek_login($username, $password, $role);

        if ($cek) {

            $this->session->set_userdata([
                "id_admin"     => $cek->id_admin,
                "nama_admin"   => $cek->nama_admin,
                "username"     => $cek->username,
                "role"         => $cek->role,
            ]);

            if ($cek->role == 'admin') {
                redirect("dashboardadmin");
            } elseif ($cek->role == 'kasir') {
                redirect("kasir/dashboardkasir");
            } elseif ($cek->role == 'dapur') {
                redirect("dapur/dashboarddapur");
            } else {
                redirect("dashboard"); 
            }
        } 
        else {
            $this->session->set_flashdata("pesan_gagal", "Username / password / role salah!");
            redirect('login');
        }
    }

    
}
