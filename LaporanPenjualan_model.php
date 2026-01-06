<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LaporanPenjualan_model extends CI_Model
{
    private $tLaporan = 'laporan_penjualan';
    private $tTrans   = 'transaksi_penjualan';
    private $tDet     = 'detail_pesanan';
    private $tUser    = 'user';
    private $table    = 'transaksi_penjualan';

    public function upsert_laporan_harian($id_user = null, $date = null)
    {
        date_default_timezone_set('Asia/Jakarta');

        $date = $date ?: date('Y-m-d');
        $periode = date('d/m/Y', strtotime($date));

        $row = $this->db
            ->select('COALESCE(SUM(total_transaksi),0) AS total', false)
            ->from($this->tTrans)
            ->where('DATE(tanggal_transaksi)', $date)
            ->get()
            ->row_array();

        $total = (float)($row['total'] ?? 0);

        $existing = $this->db
            ->get_where($this->tLaporan, ['periode_laporan' => $periode])
            ->row_array();

        $payload = [
            'id_user'         => $id_user,
            'periode_laporan' => $periode,
            'total_penjualan' => $total,
            'tanggal_cetak'   => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->where('id_laporan', $existing['id_laporan'])->update($this->tLaporan, $payload);
            return ['mode' => 'update', 'total' => $total, 'periode' => $periode];
        } else {
            $this->db->insert($this->tLaporan, $payload);
            return ['mode' => 'insert', 'total' => $total, 'periode' => $periode];
        }
    }

    public function get_daftar_transaksi()
    {
        return $this->db
            ->select("
                t.id_transaksi,
                t.id_pesanan,
                t.tanggal_transaksi,
                t.total_transaksi,
                COALESCE(u.nama_user,'-') AS nama_user,
                COALESCE(SUM(d.jumlah),0) AS jumlah_pemesanan
            ", false)
            ->from($this->tTrans.' t')
            ->join($this->tUser.' u', 'u.id_user = t.id_user', 'left')
            ->join($this->tDet.' d', 'd.id_pesanan = t.id_pesanan', 'left')
            ->group_by(['t.id_transaksi','t.id_pesanan','t.tanggal_transaksi','t.total_transaksi','u.nama_user'])
            ->order_by('t.tanggal_transaksi', 'DESC')
            ->get()
            ->result_array();
    }

    public function get_total_pendapatan()
    {
        $row = $this->db
            ->select_sum('total_transaksi', 'total')
            ->get($this->tTrans)
            ->row_array();

        return (int)($row['total'] ?? 0);
    }

    public function get_tanggal_terakhir()
    {
        $row = $this->db
            ->select('tanggal_transaksi')
            ->order_by('tanggal_transaksi', 'DESC')
            ->limit(1)
            ->get($this->tTrans)
            ->row_array();

        return $row['tanggal_transaksi'] ?? null;
    }

    public function get_pendapatan_harian($tanggal_awal = '', $tanggal_akhir = '', $limit = 30)
    {
        if (is_numeric($tanggal_awal) && $tanggal_akhir === '') {
            $limit = (int)$tanggal_awal;
            $tanggal_awal = '';
            $tanggal_akhir = '';
        }

        $this->db
            ->select("
                DATE(t.tanggal_transaksi) AS tanggal,
                COALESCE(SUM(t.total_transaksi),0) AS total_pendapatan,
                COALESCE(SUM(d.jumlah),0) AS jumlah_pemesanan
            ", false)
            ->from($this->tTrans.' t')
            ->join($this->tDet.' d', 'd.id_pesanan = t.id_pesanan', 'left');

        if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $this->db->where('t.tanggal_transaksi >=', $tanggal_awal . ' 00:00:00');
            $this->db->where('t.tanggal_transaksi <=', $tanggal_akhir . ' 23:59:59');
        }

        $this->db->group_by('DATE(t.tanggal_transaksi)')
                 ->order_by('DATE(t.tanggal_transaksi)', 'DESC');

        if (empty($tanggal_awal) || empty($tanggal_akhir)) {
            $this->db->limit((int)$limit);
        }

        return $this->db->get()->result_array();
    }

   
    public function get_pendapatan_mingguan($tanggal_awal = '', $tanggal_akhir = '', $limit = 12)
    {

        if (is_numeric($tanggal_awal) && $tanggal_akhir === '') {
            $limit = (int)$tanggal_awal;
            $tanggal_awal = '';
            $tanggal_akhir = '';
        }

        $this->db
            ->select("
                YEARWEEK(t.tanggal_transaksi,1) AS tanggal,
                COALESCE(SUM(t.total_transaksi),0) AS total_pendapatan
            ", false)
            ->from($this->tTrans.' t');

        if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $this->db->where('t.tanggal_transaksi >=', $tanggal_awal . ' 00:00:00');
            $this->db->where('t.tanggal_transaksi <=', $tanggal_akhir . ' 23:59:59');
        }

        $this->db->group_by('YEARWEEK(t.tanggal_transaksi,1)')
                 ->order_by('tanggal', 'DESC');

        if (empty($tanggal_awal) || empty($tanggal_akhir)) {
            $this->db->limit((int)$limit);
        }

        return $this->db->get()->result_array();
    }

    public function get_pendapatan_bulanan($tanggal_awal = '', $tanggal_akhir = '', $limit = 12)
    {
        if (is_numeric($tanggal_awal) && $tanggal_akhir === '') {
            $limit = (int)$tanggal_awal;
            $tanggal_awal = '';
            $tanggal_akhir = '';
        }

        $this->db
            ->select("
                DATE_FORMAT(t.tanggal_transaksi,'%Y-%m') AS tanggal,
                COALESCE(SUM(t.total_transaksi),0) AS total_pendapatan
            ", false)
            ->from($this->tTrans.' t');

        if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $this->db->where('t.tanggal_transaksi >=', $tanggal_awal . ' 00:00:00');
            $this->db->where('t.tanggal_transaksi <=', $tanggal_akhir . ' 23:59:59');
        }

        $this->db->group_by("DATE_FORMAT(t.tanggal_transaksi,'%Y-%m')")
                 ->order_by('tanggal', 'DESC');

        if (empty($tanggal_awal) || empty($tanggal_akhir)) {
            $this->db->limit((int)$limit);
        }

        return $this->db->get()->result_array();
    }

    public function get_pendapatan_hari_ini($date = null)
    {
        date_default_timezone_set('Asia/Jakarta');

        $date = $date ?: date('Y-m-d');

        $row = $this->db
            ->select('COALESCE(SUM(total_transaksi),0) AS total', false)
            ->from($this->tTrans)
            ->where('DATE(tanggal_transaksi)', $date)
            ->get()
            ->row_array();

        return (int)($row['total'] ?? 0);
    }

    public function getPaginated()
    {
        $this->load->library('pagination');

        $config['base_url']         = base_url('laporanpenjualan/index');
        $config['total_rows']       = $this->db->count_all($this->table);
        $config['per_page']         = 5;
        $config['uri_segment']      = 3;
        $config['use_page_numbers'] = TRUE;
        $config['page_query_string'] = FALSE;

        $config['first_link'] = FALSE;
        $config['last_link']  = FALSE;

        $config['prev_link'] = '&lsaquo;';
        $config['next_link'] = '&rsaquo;';

        $page = (int) $this->uri->segment(3, 1);
        if ($page < 1) $page = 1;

        $config['cur_page'] = $page;

        $offset = ($page - 1) * $config['per_page'];

        $this->pagination->initialize($config);

        $data['menu'] = $this->db
            ->order_by('tanggal_transaksi', 'DESC')
            ->limit($config['per_page'], $offset)
            ->get($this->table)
            ->result_array();

        $data['pagination'] = $this->pagination->create_links();
        $data['start']      = $offset;

        return $data;
    }

    public function get_dashboard_data_harian()
    {
        date_default_timezone_set('Asia/Jakarta');
        $hari_ini = date('Y-m-d');

        return [
            'penjualan_harian'        => $this->get_pendapatan_harian(30),
            'total_pendapatan_harian' => $this->get_pendapatan_hari_ini($hari_ini),
            'tanggal_hari_ini'        => $hari_ini,
        ];
    }

    public function get_statistik_pendapatan_harian($limit = 6)
    {
        $rows = $this->db
            ->select('DATE(tanggal_transaksi) AS tgl, SUM(total_transaksi) AS total', false)
            ->from($this->tTrans)
            ->group_by('DATE(tanggal_transaksi)')
            ->order_by('DATE(tanggal_transaksi)', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result_array();

        $stat = [];
        foreach ($rows as $r) {
            $stat[$r['tgl']] = (int)$r['total'];
        }
        return $stat;
    }

    public function get_export_data($periode, $tanggal_awal = null, $tanggal_akhir = null)
    {
        $table = $this->tTrans;
        if (empty($periode)) $periode = 'harian';

        $tanggal_ref = !empty($tanggal_awal) ? $tanggal_awal : date('Y-m-d');

        if ($periode === 'harian') {
            $start = $tanggal_ref;
            $end   = $tanggal_ref;
        } elseif ($periode === 'mingguan') {
            $start = date('Y-m-d', strtotime($tanggal_ref . ' -6 days'));
            $end   = $tanggal_ref;
        } else { // bulanan
            $start = date('Y-m-01', strtotime($tanggal_ref));
            $end   = $tanggal_ref;
            $periode = 'bulanan';
        }

        $this->db->select("DATE(tanggal_transaksi) AS tgl, SUM(total_transaksi) AS total", false);
        $this->db->from($table);
        $this->db->where('tanggal_transaksi >=', $start . ' 00:00:00');
        $this->db->where('tanggal_transaksi <=', $end . ' 23:59:59');
        $this->db->group_by("DATE(tanggal_transaksi)");
        $this->db->order_by("tgl", "ASC");

        $q = $this->db->get()->result_array();

        $out = [];
        foreach ($q as $row) {
            $out[] = [
                'label_tanggal' => date('d/m/Y', strtotime($row['tgl'])),
                'total' => (float)$row['total']
            ];
        }
        return $out;
    }

    public function log_cetak_laporan($data)
    {
        return $this->db->insert($this->tLaporan, $data);
    }

    public function hitung_total_periode($periode, $tanggal_awal = null, $tanggal_akhir = null)
    {
        $table = $this->tTrans;
        $tanggal_ref = !empty($tanggal_awal) ? $tanggal_awal : date('Y-m-d');

        if ($periode === 'harian') {
            $start = $tanggal_ref; $end = $tanggal_ref;
        } elseif ($periode === 'mingguan') {
            $start = date('Y-m-d', strtotime($tanggal_ref.' -6 days')); $end = $tanggal_ref;
        } else {
            $start = date('Y-m-01', strtotime($tanggal_ref)); $end = $tanggal_ref;
        }

        $this->db->select("SUM(total_transaksi) AS grand_total", false);
        $this->db->from($table);
        $this->db->where('tanggal_transaksi >=', $start.' 00:00:00');
        $this->db->where('tanggal_transaksi <=', $end.' 23:59:59');

        $row = $this->db->get()->row_array();
        return (float)($row['grand_total'] ?? 0);
    }
}
