<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemesanan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
        $this->load->model('Pemesanan_model', 'pemesanan');

        if (!$this->session->userdata('id_admin')) {
            $this->session->set_flashdata('pesan_gagal', 'Silakan login terlebih dahulu.');
            redirect('login');
        }

        if ($this->session->userdata('role') !== 'kasir') {
            show_error('Akses ditolak. Halaman ini khusus kasir.', 403);
        }
    }

    public function index()
    {
        $data['menus'] = $this->pemesanan->get_all_menu();
        $data['page_css'] = 'assets/css/kasir/pemesanan.css';
        $data['id_pesanan_aktif'] = (int)$this->session->userdata('kasir_id_pesanan');

        $this->load->view('header', $data);
        $this->load->view('kasir/sidebarkasir');
        $this->load->view('topnav');
        $this->load->view('kasir/pemesanan', $data);
        $this->load->view('footer');
    }

    public function store()
    {
        $id_admin = (int)$this->session->userdata('id_admin');

        $id_menu_list = $this->input->post('id_menu', true);
        $jumlah_list  = $this->input->post('jumlah', true);

        if (!is_array($id_menu_list)) $id_menu_list = [$id_menu_list];
        if (!is_array($jumlah_list))  $jumlah_list  = [$jumlah_list];

    
        $merged = []; 
        foreach ($id_menu_list as $i => $midRaw) {
            $mid = (int)$midRaw;
            $qty = isset($jumlah_list[$i]) ? max(1, (int)$jumlah_list[$i]) : 1;

            if ($mid <= 0) continue;

            if (!isset($merged[$mid])) $merged[$mid] = 0;
            $merged[$mid] += $qty;
        }

        $menu_ids = array_keys($merged);
        $qtys     = array_values($merged);


        if (count($menu_ids) === 0) {
            $this->session->set_flashdata('pesan_gagal', 'Minimal pilih 1 menu.');
            redirect('kasir/pemesanan');
        }

        $result = $this->pemesanan->create_new_order($id_admin, $menu_ids, $qtys);

        if ($result['status'] === true) {
            $this->session->set_flashdata('pesan_berhasil', 'Pesanan dibuat. ID: '.$result['id_pesanan']);
        } else {
            $this->session->set_flashdata('pesan_gagal', $result['message']);
        }

        redirect('kasir/pemesanan');
    }

    

    public function reset()
    {
        $this->session->unset_userdata('kasir_id_pesanan');
        $this->session->set_flashdata('pesan_berhasil', 'Pesanan baru dimulai.');
        redirect('kasir/pemesanan');
    }

    public function checkout()
{
    $id_pesanan = (int)$this->session->userdata('kasir_id_pesanan');
    if ($id_pesanan <= 0) {
        $this->session->set_flashdata('pesan_gagal', 'Tidak ada pesanan aktif.');
        redirect('kasir/pemesanan');
    }

 
    $this->db->where('id_pesanan', $id_pesanan)
        ->update('pesanan', ['status_pesanan' => 'selesai']);

    if ($this->db->table_exists('status_pesanan')) {

        $timeCol = null;
        foreach (['waktu_update', 'created_at', 'tanggal', 'waktu'] as $c) {
            if ($this->db->field_exists($c, 'status_pesanan')) { $timeCol = $c; break; }
        }

        $data = [
            'id_pesanan' => $id_pesanan,
            'status'     => 'selesai',
        ];

        if ($timeCol) {
            $data[$timeCol] = date('Y-m-d H:i:s');
        }

        $this->db->insert('status_pesanan', $data);
    }


    $this->session->unset_userdata('kasir_id_pesanan');

    $this->session->set_flashdata('pesan_berhasil', 'Pesanan diselesaikan. Pesanan baru dimulai.');
    redirect('kasir/pemesanan');
}

}
