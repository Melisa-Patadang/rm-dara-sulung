<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboardkasir extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

        $this->load->library('session');
        $this->load->database();
        $this->load->model('Kasir_model', 'kasir');

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
        $data['nama_admin'] = $this->session->userdata('nama_admin');

        $q    = $this->input->get('q', true);
        $sort = $this->input->get('sort', true) ?: 'desc';
        $rows = $this->kasir->get_riwayat_transaksi($q, $sort);
        $data['transaksi'] = $this->kasir->map_rows_for_view($rows);
        $data['penjualan_hari_ini']  = $this->kasir->get_penjualan_hari_ini();
        $data['pendapatan_hari_ini'] = $this->kasir->get_pendapatan_hari_ini();
        $data['total_penjualan']  = $this->kasir->get_total_penjualan();
        $data['total_pendapatan'] = $this->kasir->get_total_pendapatan();
        $trend = $this->kasir->get_income_trend_6_months();
        $data['chart_labels'] = $trend['labels'];
        $data['chart_values'] = $trend['values'];

        $data['q']    = $q;
        $data['sort'] = $sort;

        $this->load->view('header');
        $this->load->view('kasir/sidebarkasir');
        $this->load->view('topnav');
        $this->load->view('kasir/Dashboardkasir', $data);
        $this->load->view('footer');
    }
}
