<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Riwayat extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->database();
        $this->load->model('Riwayat_model', 'riwayat');

        if (!$this->session->userdata('id_admin')) {
            redirect('login');
        }
        if ($this->session->userdata('role') !== 'kasir') {
            show_error('Akses ditolak. Halaman ini khusus kasir.', 403);
        }
    }

          public function index()
        {
            $q    = (string)$this->input->get('q', true);
            $sort = (string)$this->input->get('sort', true);
            if ($sort !== 'asc' && $sort !== 'desc') $sort = 'desc';

            $result = $this->riwayat->get_pesanan_with_pagination($q, $sort);

            $data['q']          = $q;
            $data['sort']       = $sort;
            $data['diproses']   = $result['data'];
            $data['detail_map'] = $this->riwayat->get_detail_by_pesanan_ids(
                array_map(fn($p) => (int)$p->id_pesanan, $result['data'])
            );
            $data['pagination'] = $result['links'];
            $data['page_css']   = 'assets/css/kasir/riwayat.css';

            $this->load->view('header', $data);
            $this->load->view('kasir/sidebarkasir');
            $this->load->view('topnav');
            $this->load->view('kasir/riwayat', $data);
            $this->load->view('footer');
        }
   public function selesai($id_pesanan)
{
    $id = (int)$id_pesanan;
    if ($id <= 0) show_404();

    $result = $this->riwayat->selesaikan_pesanan($id);

    if (!empty($result['status'])) {
        $this->session->set_flashdata('pesan_berhasil', $result['message'] ?? 'Pesanan selesai.');
    } else {
        $this->session->set_flashdata('pesan_gagal', $result['message'] ?? 'Gagal menyelesaikan pesanan.');
    }

    redirect('kasir/riwayat');
}


}