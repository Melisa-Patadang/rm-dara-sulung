<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboardadmin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');

        if (!$this->session->userdata('id_admin')) {
            $this->session->set_flashdata('pesan_gagal', 'Silakan login terlebih dahulu.');
            redirect('login');
        }
        if ($this->session->userdata('role') !== 'admin') {
            show_error('Akses ditolak. Halaman ini khusus admin.', 403);
        }
    }

    public function index()
    {
        $data['menu'] = $this->db->limit(2)->get('menu')->result_array();
        $data['penjualan'] = $this->db->get('laporan_penjualan')->result_array();

        $this->db->select("
            DATE_FORMAT(tanggal_cetak, '%Y-%m') AS bulan_key,
            DATE_FORMAT(tanggal_cetak, '%b %Y') AS bulan_label,
            SUM(total_penjualan) AS total_bulanan
        ", false);
        $this->db->from('laporan_penjualan');
        $this->db->where('tanggal_cetak >=', date('Y-m-01', strtotime('-5 months')));
        $this->db->group_by("DATE_FORMAT(tanggal_cetak, '%Y-%m')", false);
        $this->db->order_by("DATE_FORMAT(tanggal_cetak, '%Y-%m')", 'ASC', false);

        $trend = $this->db->get()->result_array();
        $data['chart_labels'] = array_column($trend, 'bulan_label');
        $data['chart_values'] = array_map('intval', array_column($trend, 'total_bulanan'));

        $this->load->view('header');
        $this->load->view('sidebar');
        $this->load->view('topnav');
        $this->load->view('dashboardadmin', $data);
        $this->load->view('footer');

        
    }
}
