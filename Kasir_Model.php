<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir_model extends CI_Model
{
    private string $tabel = 'transaksi_penjualan';

    public function get_riwayat_transaksi(?string $q = null, string $sort = 'desc'): array
    {
        if (!$this->db->table_exists($this->tabel)) return [];

        $sort = strtolower($sort) === 'asc' ? 'ASC' : 'DESC';

        $this->db->select("
            tp.id_transaksi,
            tp.id_pesanan,
            tp.id_user,
            tp.tanggal_transaksi,
            tp.total_transaksi
        ");
        $this->db->from($this->tabel . " tp");

        if (!empty($q)) {
            $this->db->group_start();
            $this->db->like("tp.id_transaksi", $q);
            $this->db->or_like("tp.id_pesanan", $q);
            $this->db->or_like("tp.id_user", $q);
            $this->db->group_end();
        }

        $this->db->order_by("tp.tanggal_transaksi", $sort);
        $this->db->limit(5);

        return $this->db->get()->result_array();
    }

    public function get_income_trend_6_months(): array
    {
        if (!$this->db->table_exists($this->tabel)) {
            return ['labels' => [], 'values' => []];
        }

        $this->db->select("
            DATE_FORMAT(tanggal_transaksi, '%Y-%m') AS bulan_key,
            DATE_FORMAT(tanggal_transaksi, '%b %Y') AS bulan_label,
            SUM(total_transaksi) AS total_bulanan
        ", false);

        $this->db->from($this->tabel);
        $this->db->where("tanggal_transaksi >=", date('Y-m-01', strtotime('-5 months')));
        $this->db->group_by("DATE_FORMAT(tanggal_transaksi, '%Y-%m')", false);
        $this->db->order_by("DATE_FORMAT(tanggal_transaksi, '%Y-%m')", 'ASC', false);

        $trend = $this->db->get()->result_array();

        return [
            'labels' => array_column($trend, 'bulan_label'),
            'values' => array_map('intval', array_column($trend, 'total_bulanan')),
        ];
    }

    public function map_rows_for_view(array $rows): array
    {
        return array_map(function($r){
            $id = (int)($r['id_transaksi'] ?? 0);
            $kode = $id ? 'TRX-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT) : '-';

            return [
                'kode'              => $kode,
                'id_pesanan'        => $r['id_pesanan'] ?? '-',
                'id_user'           => $r['id_user'] ?? '-',
                'total_transaksi'   => (int)($r['total_transaksi'] ?? 0),
                'tanggal_transaksi' => $r['tanggal_transaksi'] ?? '-',
            ];
        }, $rows);
    }

    public function get_penjualan_hari_ini(): int
    {
        $today = date('Y-m-d');

        if ($this->db->table_exists($this->tabel)) {
            return (int)$this->db
                ->from($this->tabel)
                ->where('DATE(tanggal_transaksi)', $today)
                ->count_all_results();
        }

        if ($this->db->table_exists('pesanan')) {
            return (int)$this->db
                ->from('pesanan')
                ->where('DATE(tanggal_pesan)', $today)
                ->where('status_pesanan', 'selesai')
                ->count_all_results();
        }

        return 0;
    }

    public function get_pendapatan_hari_ini(): int
    {
        $today = date('Y-m-d');

        if ($this->db->table_exists($this->tabel)) {
            $row = $this->db
                ->select('SUM(total_transaksi) AS total', false)
                ->from($this->tabel)
                ->where('DATE(tanggal_transaksi)', $today)
                ->get()->row();

            return (int)($row->total ?? 0);
        }

        if ($this->db->table_exists('pesanan')) {
            $row = $this->db
                ->select('SUM(total_harga) AS total', false)
                ->from('pesanan')
                ->where('DATE(tanggal_pesan)', $today)
                ->where('status_pesanan', 'selesai')
                ->get()->row();

            return (int)($row->total ?? 0);
        }

        return 0;
    }


    public function get_total_penjualan(): int
    {
        if (!$this->db->table_exists($this->tabel)) return 0;
        return (int)$this->db->from($this->tabel)->count_all_results();
    }

    public function get_total_pendapatan(): int
    {
        if (!$this->db->table_exists($this->tabel)) return 0;

        $row = $this->db
            ->select('SUM(total_transaksi) AS total', false)
            ->from($this->tabel)
            ->get()->row();

        return (int)($row->total ?? 0);
    }
}
