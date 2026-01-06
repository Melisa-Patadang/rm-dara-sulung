<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemesanan_model extends CI_Model
{
    public function get_all_menu() {
        return $this->db->order_by('nama_menu', 'ASC')->get('menu')->result();
    }

    public function create_order_header(int $id_admin): int {
    $id_pelanggan = 1;
    $this->db->insert('pesanan', [
        'id_pelanggan'   => $id_pelanggan,
        'tanggal_pesan'  => date('Y-m-d H:i:s'),
        'total_harga'    => 0,
        'status_pesanan' => 'diproses'
    ]);
        return (int)$this->db->insert_id();
    }

    public function get_menu_prices(array $menu_ids): array {
        if (empty($menu_ids)) return [];

        $rows = $this->db->select('id_menu, harga')
            ->from('menu')
            ->where_in('id_menu', $menu_ids)
            ->get()->result_array();

        $harga_map = [];
        foreach ($rows as $r) {
            $harga_map[(int)$r['id_menu']] = (float)$r['harga'];
        }
        return $harga_map;
    }

    public function insert_detail_batch(int $id_pesanan, array $detail_batch): bool {
        if (empty($detail_batch)) return false;
        return (bool)$this->db->insert_batch('detail_pesanan', $detail_batch);
    }

    public function update_total(int $id_pesanan): void {
        $sum = $this->db->select_sum('subtotal', 'total')
            ->from('detail_pesanan')
            ->where('id_pesanan', $id_pesanan)
            ->get()->row_array();

        $total = (float)($sum['total'] ?? 0);

        $this->db->where('id_pesanan', $id_pesanan)
                 ->update('pesanan', ['total_harga' => $total]);
    }

    public function save_items_with_session_order(int $id_admin, int $id_pesanan, array $menu_ids, array $jumlah_list = []): array {
        $this->db->trans_begin();
        if ($id_pesanan <= 0) {
            $id_pesanan = $this->create_order_header($id_admin);
            if ($id_pesanan <= 0) {
                $this->db->trans_rollback();
                return ['status' => false, 'message' => 'Gagal membuat pesanan baru.'];
            }
        }
        $harga_map = $this->get_menu_prices($menu_ids);
        if (empty($harga_map)) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Menu tidak ditemukan di database.'];
        }
        $detail_batch = [];
        foreach ($menu_ids as $index => $mid) {
            $mid = (int)$mid;
            if ($mid <= 0 || !isset($harga_map[$mid])) continue;

            $qty = 1;
            if (isset($jumlah_list[$index])) {
                $qty = max(1, (int)$jumlah_list[$index]);
            }

            $subtotal = $harga_map[$mid] * $qty;

            $detail_batch[] = [
                'id_pesanan' => $id_pesanan,
                'id_menu'    => $mid,
                'jumlah'     => $qty,
                'subtotal'   => $subtotal,
            ];
        }
        if (empty($detail_batch)) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Tidak ada item valid untuk disimpan.'];
        }
        $this->insert_detail_batch($id_pesanan, $detail_batch);
        $this->update_total($id_pesanan);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Gagal menyimpan pesanan.'];
        }
        $this->db->trans_commit();
        return ['status' => true, 'id_pesanan' => $id_pesanan];
        }

    public function create_new_order(int $id_admin, array $menu_ids, array $jumlah_list = []): array {
    
    $this->db->trans_begin();
    $id_pelanggan = 1;
    $this->db->insert('pesanan', [
        'id_pelanggan'   => $id_pelanggan,
        'tanggal_pesan'  => date('Y-m-d H:i:s'),
        'total_harga'    => 0,
        'status_pesanan' => 'diproses'
    ]);
    $id_pesanan = (int)$this->db->insert_id();
    if ($id_pesanan <= 0) {
        $this->db->trans_rollback();
        return ['status' => false, 'message' => 'Gagal membuat pesanan baru.'];
    }
    $harga_map = $this->get_menu_prices($menu_ids);
    if (empty($harga_map)) {
        $this->db->trans_rollback();
        return ['status' => false, 'message' => 'Menu tidak ditemukan di database.'];
    }
    $detail_batch = [];
    foreach ($menu_ids as $index => $mid) {
        $mid = (int)$mid;
        if (!isset($harga_map[$mid])) continue;

        $qty = 1;
        if (isset($jumlah_list[$index])) $qty = max(1, (int)$jumlah_list[$index]);

        $subtotal = $harga_map[$mid] * $qty;

        $detail_batch[] = [
            'id_pesanan' => $id_pesanan,
            'id_menu'    => $mid,
            'jumlah'     => $qty,
            'subtotal'   => $subtotal,
        ];
    }
    if (empty($detail_batch)) {
        $this->db->trans_rollback();
        return ['status' => false, 'message' => 'Tidak ada item valid untuk disimpan.'];
    }
    $this->db->insert_batch('detail_pesanan', $detail_batch);
    $this->update_total($id_pesanan);

    if ($this->db->trans_status() === false) {
        $this->db->trans_rollback();
        return ['status' => false, 'message' => 'Gagal menyimpan pesanan.'];
    }
    $this->db->trans_commit();
    return ['status' => true, 'id_pesanan' => $id_pesanan];
    }

}
