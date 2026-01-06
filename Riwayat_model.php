<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Riwayat_model extends CI_Model
{
    public function get_pesanan_diproses(string $q = '', string $sort = 'desc', int $limit = 0, int $offset = 0)
    {
        $sort = strtolower($sort) === 'asc' ? 'ASC' : 'DESC';
        $q = trim($q);

        $this->db->select('p.id_pesanan, p.tanggal_pesan, p.total_harga, p.status_pesanan');
        $this->db->from('pesanan p');
        $this->db->where_in('p.status_pesanan', ['diproses', 'siap_ambil']);

        if ($q !== '') {
            $this->db->join('detail_pesanan dp', 'dp.id_pesanan = p.id_pesanan', 'left');
            $this->db->join('menu m', 'm.id_menu = dp.id_menu', 'left');

            $this->db->group_start();
            if (ctype_digit((string)$q)) {
                $this->db->or_where('p.id_pesanan', (int)$q);
            }
            $this->db->or_like('m.nama_menu', $q);
            $this->db->group_end();

            $this->db->group_by('p.id_pesanan');
        }

        $this->db->order_by('p.tanggal_pesan', $sort);

        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function get_pesanan_with_pagination(string $q = '', string $sort = 'desc'): array
    {
        $this->load->library('pagination');

        $total_rows = $this->count_pesanan_diproses($q);

        $config['base_url']            = site_url('kasir/riwayat');
        $config['total_rows']          = $total_rows;
        $config['per_page']            = 5;
        $config['page_query_string']   = TRUE;
        $config['query_string_segment'] = 'per_page';

        $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul>';
        $config['cur_tag_open']    = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close']   = '</span></li>';
        $config['num_tag_open']    = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close']   = '</span></li>';
        $config['next_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close']  = '</span></li>';
        $config['prev_tag_open']   = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close']  = '</span></li>';

        $this->pagination->initialize($config);

        $page = (int)($this->input->get('per_page') ?? 0);
        if ($page < 0) $page = 0;

        $data = $this->get_pesanan_diproses($q, $sort, (int)$config['per_page'], $page);

        return [
            'data'  => $data,
            'links' => $this->pagination->create_links()
        ];
    }

    public function get_detail_by_pesanan_ids(array $ids): array
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($v){ return $v > 0; });
        $ids = array_values($ids);

        if (empty($ids)) return [];

        $rows = $this->db->select('dp.id_pesanan, m.nama_menu, dp.jumlah')
            ->from('detail_pesanan dp')
            ->join('menu m', 'm.id_menu = dp.id_menu', 'left')
            ->where_in('dp.id_pesanan', $ids)
            ->order_by('dp.id_pesanan', 'DESC')
            ->get()
            ->result_array();

        $grouped = [];
        foreach ($rows as $r) {
            $pid = (int)$r['id_pesanan'];
            if (!isset($grouped[$pid])) $grouped[$pid] = [];
            $grouped[$pid][] = [
                'nama_menu' => $r['nama_menu'] ?? '(menu)',
                'jumlah'    => (int)$r['jumlah'],
            ];
        }

        return $grouped;
    }

    public function sudah_ada_transaksi(int $id_pesanan): bool
    {
        return $this->db->from('transaksi_penjualan')
            ->where('id_pesanan', $id_pesanan)
            ->count_all_results() > 0;
    }

    public function selesaikan_pesanan($id_pesanan): array
    {
        $id_pesanan = (int)$id_pesanan;

        $pesanan = $this->db->from('pesanan')
            ->where('id_pesanan', $id_pesanan)
            ->get()
            ->row_array();

        if (!$pesanan) {
            return ['status' => false, 'message' => 'Pesanan tidak ditemukan.'];
        }

        $status_pesanan = (string)($pesanan['status_pesanan'] ?? ($pesanan['status'] ?? ''));
        if ($status_pesanan !== 'siap_ambil') {
            return ['status' => false, 'message' => 'Pesanan belum SIAP AMBIL.'];
        }

        if ($this->sudah_ada_transaksi($id_pesanan)) {
            return ['status' => false, 'message' => 'Transaksi untuk pesanan ini sudah dibuat sebelumnya.'];
        }
        $id_user = 0;
        foreach (['member_id', 'id_member', 'user_id', 'id_user'] as $k) {
            if (isset($pesanan[$k]) && is_numeric($pesanan[$k])) {
                $id_user = (int)$pesanan[$k];
                if ($id_user > 0) break;
            }
        }


        if ($id_user > 0 && $this->db->table_exists('user') && $this->db->field_exists('id_user', 'user')) {
            $u = $this->db->get_where('user', ['id_user' => $id_user])->row_array();
            if (!$u) $id_user = 0;
        } else {
            $id_user = 0;
        }

        if ($id_user <= 0 && $this->db->table_exists('user') && $this->db->field_exists('id_user', 'user')) {
            $row = $this->db->select('id_user')->from('user')->order_by('id_user', 'ASC')->limit(1)->get()->row_array();
            $id_user = (int)($row['id_user'] ?? 0);
        }

        $total = 0;
        foreach (['total_transaksi', 'total_harga', 'total_amount', 'total'] as $k) {
            if (isset($pesanan[$k]) && is_numeric($pesanan[$k])) {
                $total = (float)$pesanan[$k];
                break;
            }
        }

        $this->db->trans_begin();
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


        if ($id_user > 0) {
            $this->db->insert('transaksi_penjualan', [
                'id_pesanan'        => $id_pesanan,
                'id_user'           => $id_user,
                'tanggal_transaksi' => date('Y-m-d H:i:s'),
                'total_transaksi'   => $total
            ]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Gagal menyelesaikan pesanan.'];
        }

        $this->db->trans_commit();

        if ($id_user <= 0) {
            return ['status' => true, 'message' => 'Pesanan selesai. (Transaksi tidak dibuat karena ID user tidak valid di tabel user)'];
        }

        return ['status' => true, 'message' => 'Pesanan berhasil diselesaikan.'];
    }


    public function count_pesanan_diproses(string $q = ''): int
    {
        $q = trim($q);

        $this->db->from('pesanan p');
        $this->db->where_in('p.status_pesanan', ['diproses', 'siap_ambil']);

        if ($q !== '') {
            $this->db->join('detail_pesanan dp', 'dp.id_pesanan = p.id_pesanan', 'left');
            $this->db->join('menu m', 'm.id_menu = dp.id_menu', 'left');

            $this->db->group_start();
            if (ctype_digit((string)$q)) {
                $this->db->or_where('p.id_pesanan', (int)$q);
            }
            $this->db->or_like('m.nama_menu', $q);
            $this->db->group_end();

            $this->db->group_by('p.id_pesanan');
            return count($this->db->get()->result());
        }

        return (int)$this->db->count_all_results();
    }
}
