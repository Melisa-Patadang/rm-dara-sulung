<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporanpenjualan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');

        $this->load->library('session');
        $this->load->database();

        if (!$this->session->userdata('id_admin')) {
            $this->session->set_flashdata('pesan_gagal', 'Silakan login terlebih dahulu.');
            redirect('login');
        }

        $this->load->model('LaporanPenjualan_model', 'laporan');
    }

    private function normalize_date($s)
    {
        $s = trim((string)$s);
        if ($s === '') return '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
            $dt = DateTime::createFromFormat('d/m/Y', $s);
            return $dt ? $dt->format('Y-m-d') : '';
        }

        $t = strtotime($s);
        return $t ? date('Y-m-d', $t) : '';
    }

    private function format_label_by_periode($periode, $label)
    {
        $label = trim((string)$label);

        if ($periode === 'mingguan') {
            if (preg_match('/^\d{6}$/', $label)) {
                $year = substr($label, 0, 4);
                $week = (int)substr($label, 4, 2);
                return 'Minggu ' . $week . ' (' . $year . ')';
            }
            return $label;
        }
        if ($periode === 'bulanan') {
            if (preg_match('/^\d{4}-\d{2}$/', $label)) {
                return date('F Y', strtotime($label . '-01'));
            }
            if (preg_match('/^\d{6}$/', $label)) {
                $year = substr($label, 0, 4);
                $mon  = substr($label, 4, 2);
                return date('F Y', strtotime($year . '-' . $mon . '-01'));
            }
            return $label;
        }
        if ($periode === 'harian') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $label)) {
                return date('d/m/Y', strtotime($label));
            }
            return $label;
        }

        return $label;

    }
    
    private function build_export_rows($periode, $tanggal_awal, $tanggal_akhir)
    {
        if ($periode === 'mingguan') {
            $laporan = $this->laporan->get_pendapatan_mingguan($tanggal_awal, $tanggal_akhir);
        } elseif ($periode === 'bulanan') {
            $laporan = $this->laporan->get_pendapatan_bulanan($tanggal_awal, $tanggal_akhir);
        } else {
            $laporan = $this->laporan->get_pendapatan_harian($tanggal_awal, $tanggal_akhir);
        }

        $rows = [];
        foreach ((array)$laporan as $r) {
            $raw_label = $r['tanggal'] ?? $r['label_tanggal'] ?? '';
            $total     = $r['total_pendapatan'] ?? $r['total_transaksi'] ?? $r['total'] ?? 0;

            $rows[] = [
                'label_tanggal' => $this->format_label_by_periode($periode, $raw_label),
                'total'         => (float)$total,
            ];
        }

        return $rows;
    }

    private function safe_log_cetak($periode_db, $total_penjualan)
    {
        $id_user = (int)$this->session->userdata('id_user');
        if ($id_user <= 0) return;

        $exists = $this->db->where('id_user', $id_user)->count_all_results('user') > 0;
        if (!$exists) return;

        $this->laporan->log_cetak_laporan([
            'id_user'         => $id_user,
            'periode_laporan' => $periode_db,
            'total_penjualan' => $total_penjualan,
            'tanggal_cetak'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function index()
    {
        $periode       = (string)$this->input->post('periode', true);
        $tanggal_awal  = $this->normalize_date($this->input->post('tanggal_awal', true));
        $tanggal_akhir = $this->normalize_date($this->input->post('tanggal_akhir', true));

        if (!in_array($periode, ['harian','mingguan','bulanan'], true)) {
            $periode = 'harian';
        }
        if ($periode === 'harian') {
            if ($tanggal_awal === '') {
                $tanggal_awal = date('Y-m-d');
            }
            $tanggal_akhir = $tanggal_awal;
        }

        if ($periode === 'mingguan') {
            $laporan = $this->laporan->get_pendapatan_mingguan($tanggal_awal, $tanggal_akhir);
            $labels  = array_column($laporan, 'tanggal');
            $values  = array_column($laporan, 'total_pendapatan');
            $total   = array_sum($values);
        } elseif ($periode === 'bulanan') {
            $laporan = $this->laporan->get_pendapatan_bulanan($tanggal_awal, $tanggal_akhir);
            $labels  = array_column($laporan, 'tanggal');
            $values  = array_column($laporan, 'total_pendapatan');
            $total   = array_sum($values);
        } else {
            $laporan = $this->laporan->get_pendapatan_harian($tanggal_awal, $tanggal_akhir);
            $labels  = array_column($laporan, 'tanggal');
            $values  = array_column($laporan, 'total_pendapatan');

            $total = array_sum($values);
        }

        $paginated = $this->laporan->getPaginated();

        $data = [
            'laporan'                 => $laporan,
            'labels'                  => $labels,
            'values'                  => $values,
            'total_pendapatan_harian' => $total,
            'penjualan_harian'        => $paginated['menu'],
            'pagination'              => $paginated['pagination'],
            'start'                   => $paginated['start'],

            'periode_selected' => $periode,
            'tanggal_awal'     => $tanggal_awal,
            'tanggal_akhir'    => $tanggal_akhir,
        ];

        $this->load->view('header');
        $this->load->view('sidebar');
        $this->load->view('topnav');
        $this->load->view('laporanpenjualan', $data);
        $this->load->view('footer');
    }

    public function export_csv()
    {
        $periode       = (string)($this->input->get('periode', true) ?: $this->input->post('periode', true));
        $tanggal_awal  = $this->normalize_date($this->input->get('tanggal_awal', true) ?: $this->input->post('tanggal_awal', true));
        $tanggal_akhir = $this->normalize_date($this->input->get('tanggal_akhir', true) ?: $this->input->post('tanggal_akhir', true));

        if (!in_array($periode, ['harian','mingguan','bulanan'], true)) {
            $periode = 'harian';
        }
        if ($periode === 'harian') {
            if ($tanggal_awal === '') $tanggal_awal = date('Y-m-d');
            $tanggal_akhir = $tanggal_awal;
        }

        $rows = $this->build_export_rows($periode, $tanggal_awal, $tanggal_akhir);

        $total_penjualan = 0;
        foreach ($rows as $r) {
            $total_penjualan += (float)($r['total'] ?? 0);
        }

        $periode_map = ['harian'=>1,'mingguan'=>2,'bulanan'=>3];
        $periode_db  = $periode_map[$periode] ?? 1;

        $this->safe_log_cetak($periode_db, $total_penjualan);

        $filename = 'laporan_penjualan_' . $periode . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, ['No', 'Tanggal/Periode', 'Total Transaksi']);

        $no = 1;
        foreach ($rows as $r) {
            fputcsv($out, [
                $no++,
                $r['label_tanggal'] ?? '-',
                $r['total'] ?? 0
            ]);
        }

        fclose($out);
        exit;
    }

    public function export_pdf()
    {
        $periode       = (string)($this->input->get('periode', true) ?: $this->input->post('periode', true));
        $tanggal_awal  = $this->normalize_date($this->input->get('tanggal_awal', true) ?: $this->input->post('tanggal_awal', true));
        $tanggal_akhir = $this->normalize_date($this->input->get('tanggal_akhir', true) ?: $this->input->post('tanggal_akhir', true));

        if (!in_array($periode, ['harian','mingguan','bulanan'], true)) {
            $periode = 'harian';
        }

        if ($periode === 'harian') {
            if ($tanggal_awal === '') $tanggal_awal = date('Y-m-d');
            $tanggal_akhir = $tanggal_awal;
        }

        $rows = $this->build_export_rows($periode, $tanggal_awal, $tanggal_akhir);

        $total_penjualan = 0;
        foreach ($rows as $r) {
            $total_penjualan += (float)($r['total'] ?? 0);
        }

        $periode_map = ['harian'=>1,'mingguan'=>2,'bulanan'=>3];
        $periode_db  = $periode_map[$periode] ?? 1;

        $this->safe_log_cetak($periode_db, $total_penjualan);

        $this->load->library('pdf');

        $data = [
            'periode'          => $periode,
            'tanggal_awal'     => $tanggal_awal,
            'tanggal_akhir'    => $tanggal_akhir,
            'rows'             => $rows,
            'total_penjualan'  => $total_penjualan,
            'printed_at'       => date('d/m/Y H:i'), // WIB
        ];

        $filename = 'laporan_penjualan_' . $periode . '_' . date('Ymd_His') . '.pdf';

        $this->pdf->load_view('laporanpenjualan_pdf', $data, 'A4', 'portrait', $filename);
        exit;
    }
}
